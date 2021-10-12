<?php

namespace App\Http;

use App\Document\Song;
use App\Http\Exception\InvalidResponseException;
use App\Kodi\EntityFields;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class Kodi
{
    /**
     * @var string
     */
    private $protocol;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var Client|null
     */
    private $client;

    /**
     * @var CacheInterface
     */
    private $redis;

    /**
     * @var array
     */
    private $configOverride;

    public function __construct(
        CacheInterface $redis,
        string $protocol,
        string $host,
        int $port,
        string $user = null,
        string $password = null,
        array $configOverride = []
    )
    {
        $this->protocol            = $protocol;
        $this->host                = $host;
        $this->port                = $port;
        $this->redis               = $redis;
        $this->user                = $user;
        $this->password            = $password;
        $this->configOverride      = $configOverride;
    }

    /**
     * Kodi gives us times like this:
     * [
     *    'hours' => 0,
     *    'milliseconds' => 758,
     *    'minutes' => 0,
     *    'seconds' => 1,
     * ]
     *
     * @param array $time
     *
     * @return \DateTime
     */
    public static function createDateTimeFromKodiFormat(array $time): \DateTime
    {
        // Milliseconds are only added with PHP 7.3
        $time['microseconds'] = $time['milliseconds'] * 1000;

        return \DateTime::createFromFormat('H:i:s:u', sprintf(
            '%02d:%02d:%02d:%d', $time['hours'], $time['minutes'], $time['seconds'], $time['microseconds']
        ));
    }

    /**
     * @param \DateTime $time
     *
     * @return array
     */
    public static function createKodiTimeFromDateTime(\DateTime $time): array
    {
        $time = [
            'hours'        => $time->format('H'),
            'minutes'      => $time->format('i'),
            'seconds'      => $time->format('s'),
            'milliseconds' => $time->format('u') >= 1000 ? round($time->format('u') / 1000) : 0,
        ];

        array_walk($time, function (&$entry) {
            $entry = (int)ltrim($entry, '0');
        });

        return $time;
    }

    public function left()
    {
        return $this->remoteCall('Input.ExecuteAction', [
            'action' => 'stepback',
        ]);
    }

    public function right()
    {
        return $this->remoteCall('Input.ExecuteAction', [
            'action' => 'stepforward',
        ]);
    }

    public function setSubtitles(string $state)
    {
        $allowedStates = ['previous', 'next', 'off', 'on'];

        if (!is_numeric($state) && !in_array($state, $allowedStates)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected either integer value or one of %s, got %s',
                implode(', ', $allowedStates),
                $state
            ));
        }

        return $this->remoteCall('Player.SetSubtitle', [
            'playerid' => 1,
            'subtitle' => $state,
        ]);
    }

    public function getActivePlayers()
    {
        return $this->remoteCall('Player.GetActivePlayers', []);
    }

    public function getPlayerProperties(array $properties)
    {
        return $this->remoteCall('Player.GetProperties', [
            'playerid'   => 1,
            'properties' => array_unique($properties),
        ]);
    }

    public function getPlayerItems(array $items)
    {
        return $this->remoteCall('Player.GetItem', [
            'playerid'   => 1,
            'properties' => array_unique($items),
        ])['item'];
    }

    public function openSingleSong(int $itemId, int $playlistId = 0)
    {
        $this->clearPlaylist($playlistId);

        $this->remoteCall('Playlist.Insert', [
            'position' => 0,
            'playlistid' => $playlistId,
            'item' => [
                'songid' => $itemId,
            ],
        ]);

        $this->remoteCall('Player.Open', [
            'item' => [
                'playlistid' => $playlistId,
                'position' => 0,
            ],
        ]);
    }

    public function openPlaylistItem(int $playlistId, int $position)
    {
        $this->remoteCall('Player.Open', [
            'item' => [
                'playlistid' => $playlistId,
                'position' => $position,
            ],
        ]);
    }

    public function clearPlaylist(int $playlistId = 0)
    {
        $this->remoteCall('Playlist.Clear', [
            'playlistid' => $playlistId,
        ]);
    }

    public function setPlaylist(array $items, int $playlistId = 0)
    {
        $this->clearPlaylist();
        $this->remoteCall('Playlist.Insert', [
            'position' => 0,
            'playlistid' => $playlistId,
            'item' => $items,
        ]);
    }

    public function playPause(int $playerId = 0)
    {
        return $this->remoteCall('Player.PlayPause', [
            'playerid' => $playerId,
        ]);
    }

    public function play(int $playerId = 0)
    {
        return $this->remoteCall('Player.SetSpeed', [
            'playerid' => $playerId,
            'speed'    => 1,
        ]);
    }

    public function pause(int $playerId = 0)
    {
        return $this->remoteCall('Player.SetSpeed', [
            'playerid' => $playerId,
            'speed'    => 0,
        ]);
    }

    public function seekTime(array $time, int $playerId = 0)
    {
        return $this->remoteCall('Player.Seek', [
            'playerid' => $playerId,
            'value'    => $time,
        ]);
    }

    public function scanVideoLibrary(string $directory)
    {
        return $this->remoteCall('VideoLibrary.Scan', [
            'directory' => $directory,
        ]);
    }

    public function scanAudioLibrary()
    {
        return $this->remoteCall('AudioLibrary.Scan', []);
    }

    public function cleanAudioLibrary()
    {
        return $this->remoteCall('AudioLibrary.Clean', []);
    }

    public function getShows(int $start = 0, int $end = 0)
    {
        return $this->remoteCall('VideoLibrary.GetTVShows', [
            'properties' => EntityFields::SHOW,
            'limits' => [
                'start' => $start,
                'end' => $end,
            ]
        ]);
    }

    public function getEpisodes(int $showId)
    {
        return $this->remoteCall('VideoLibrary.GetEpisodes', [
            'properties' => EntityFields::EPISODE,
            'tvshowid' => $showId,
        ]);
    }

    public function getSongs(int $start = 0, int $end = 0)
    {
        return $this->remoteCall('AudioLibrary.GetSongs', [
            'properties' => EntityFields::SONG,
            'limits' => [
                'start' => $start,
                'end' => $end,
            ]
        ]);
    }

    public function getSongDetails(int $songId)
    {
        return $this->remoteCall('AudioLibrary.GetSongDetails', [
            'songid' => $songId,
            'properties' => EntityFields::SONG,
        ])['songdetails'];
    }

    public function getArtists(int $start = 0, int $end = 0)
    {
        return $this->remoteCall('AudioLibrary.GetArtists', [
            'properties' => EntityFields::ARTIST,
            'limits' => [
                'start' => $start,
                'end' => $end,
            ]
        ]);
    }

    public function getArtistDetails(int $artistId)
    {
        return $this->remoteCall('AudioLibrary.GetArtistDetails', [
            'artistid' => $artistId,
            'properties' => EntityFields::ARTIST,
        ])['artistdetails'];
    }

    public function getRecentlyAddedSongs(int $limit = 20)
    {
        return $this->remoteCall('AudioLibrary.GetRecentlyAddedSongs', [
            'properties' => EntityFields::SONG,
            'limits' => [
                'start' => 0,
                'end' => $limit,
            ]
        ]);
    }

    public function getMovies(int $start = 0, int $end = 0): array
    {
        return $this->remoteCall('VideoLibrary.GetMovies', [
            'properties' => EntityFields::MOVIE,
            'limits' => [
                'start' => $start,
                'end' => $end,
            ]
        ]);
    }

    public function getMediaSources(): array
    {
        return $this->redis->get('kodi_sources', function (ItemInterface $item) {
            $item->expiresAt(new \DateTime('+2 day'));

            $res     = $this->remoteCall('Files.GetSources', ['video']);
            $sources = [];

            if (!empty($res['sources'])) {
                foreach ($res['sources'] as $source) {
                    $sources[$source['label']] = $source['file'];
                }
            }

            return $sources;
        });
    }

    public function getMediaSourcePath(string $source): string
    {
        if (isset($this->configOverride['media_source_path'])) {
            return $this->configOverride['media_source_path'];
        }

        $sources = $this->getMediaSources();

        if (!array_key_exists($source, $sources)) {
            throw new \Exception(sprintf('Media source "%s" does not exist in Kodi instance.', $source));
        }

        return rtrim($sources[$source], '/');
    }

    public function getKodiBaseUrl($withCredential = false)
    {
        if ($withCredential) {
            return sprintf(
                '%s://%s:%s@%s:%d',
                $this->protocol,
                $this->user,
                $this->password,
                $this->host,
                $this->port
            );
        }

        return sprintf(
            '%s://%s:%d',
            $this->protocol,
            $this->host,
            $this->port
        );
    }

    public function downloadSong(Song $song, string $localPath): ResponseInterface
    {
        return $this->getClient()->get($song->getPlayableFile(), ['save_to' => $localPath]);
    }

    public function kodiFile(?string $value, $withCredentials = false): string
    {
        if (!$value) {
            return '';
        }

        return $this->getKodiBaseUrl($withCredentials) . '/vfs/' . str_replace('+', '%20', rawurlencode($value));
    }

    public function kodiImage($value)
    {
        return empty($value)
            ? null
            : str_replace('image://', '', rtrim(urldecode($value), '/'))
        ;
    }

    /**
     * Steps:
     * 1. Encode everything after "image://music@" BUT NOT THE SLASH AT THE END
     * 2. Prepend "image://music@" and append the slash
     * 3. Encode everything again
     * 4. Prepend the host and path "http://192.168.2.100:8080/image/"
     *
     * @param $value
     * @param bool $withCredentials
     *
     * @return string
     */
    public function createFilesystemProxyLink($value, $withCredentials = false)
    {
        if (empty($value)) {
            return '';
        }

        // Decode two times to be sure its decoded
        $path = rawurldecode(rawurldecode($value));

        // Remove the slash at the end
        $path = rtrim($path, '/');

        // Find the location "image://music@"
        preg_match('/image:\/\/\w*@*/', $path, $location);
        $location = $location[0] ?? '';

        // Strip the location and we have the real path
        $path = str_replace($location, '', $path);

        // Encode the path alone
        $path = rawurlencode($path);

        // Prepend the location, append the slash, and encode again
        $url = rawurlencode($location . $path . '/');

        // Prepend the base url
        $url = $this->getKodiBaseUrl($withCredentials) . '/image/' . $url;

        return $url;
    }

    /**
     * @param string $method
     * @param array  $params
     * @param int    $playerId
     *
     * @return mixed
     * @throws InvalidResponseException
     */
    private function remoteCall(string $method, array $params, int $playerId = 1)
    {
        $json = [
            'jsonrpc' => '2.0',
            'id'      => (string)$playerId,
            'method'  => $method,
            'params'  => $params,
        ];

        $res = $this->getClient()->post('', [
            RequestOptions::BODY => json_encode($json),
        ]);

        $content = $this->validateResponse($res);

        return $content;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return mixed
     * @throws InvalidResponseException
     */
    private function validateResponse(ResponseInterface $response)
    {
        $errors = [];
        $body   = json_decode($response->getBody()->getContents(), true);

        if (($statusCode = $response->getStatusCode()) != 200) {
            $errors[] = 'Received HTTP Status Code: ' . $statusCode;
        }

        if (empty($body)) {
            $errors[] = 'Received empty body';
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            $errors[] = json_last_error_msg();
        }

        if (empty($body['result']) && !empty($body['error']['message'])) {
            $errors[] = $body['error']['message'];
        }

        if (!empty($errors)) {
            $ex = new InvalidResponseException($errors);

            throw $ex;
        }

        return $body['result'];
    }

    private function getClient(): Client
    {
        if ($this->client instanceof Client) {
            return $this->client;
        }

        $baseUri = sprintf('%s://%s:%d/jsonrpc', $this->protocol, $this->host, $this->port);

        $options = [
            'base_uri'              => $baseUri,
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
            ],
        ];

        if ($this->user && $this->password) {
            $options[RequestOptions::AUTH] = [
                $this->user, $this->password,
            ];
        }

        $this->client = new Client($options);

        return $this->client;
    }
}