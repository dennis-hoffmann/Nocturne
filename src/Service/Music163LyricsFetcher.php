<?php

namespace App\Service;

use App\Document\Song;
use App\Exception\Music163Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class Music163LyricsFetcher
{
    const ENDPOINT_SEARCH = 'https://music.163.com/api/search/get';

    const ENDPOINT_LYRIC = 'http://music.163.com/api/song/lyric';
    /**
     * @var CacheInterface
     */
    private $redis;

    public function __construct(CacheInterface $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @var Client|null
     */
    private $client;

    public function findSongCandidates(Song $song): array
    {
        $cacheKey = sprintf('song_candidates__music163__%s', $song->getId());

        return $this->redis->get($cacheKey, function (ItemInterface $item) use ($song) {
            $item->expiresAt(new \DateTime('now +1 day'));
            $query = sprintf('%s %s', SongTitleNormalizer::normalize($song->getTitle()), $song->getArtistname());

            $json = $this->request(self::ENDPOINT_SEARCH, [
                RequestOptions::QUERY => [
                    's' => $query,
                    'type' => 1
                ]
            ]);

            return $json['result']['songs'] ?? [];
        });
    }

    public function findSongLyrics(int $externalSongId): ?string
    {
        $cacheKey = sprintf('lyrics__music163__%s', $externalSongId);

        return $this->redis->get($cacheKey, function (ItemInterface $item) use ($externalSongId) {
            $item->expiresAt(new \DateTime('now +1 day'));

            $json = $this->request(self::ENDPOINT_LYRIC, [
                RequestOptions::QUERY => [
                    'id' => $externalSongId,
                    'lv' => '-1',
                    'kv' => '-1',
                    'tv' => '-1',
                ]
            ]);

            return $json['lrc']['lyric'] ?? null;
        });
    }

    private function request(string $endpoint, array $options = []): array
    {
        try {
            $res = $this->getClient()->get($endpoint, $options);
        } catch (ClientException $e) {
            if (!$e->hasResponse()) {
                throw new Music163Exception(sprintf('Received no response from music.163.com'));
            }

            $response = $e->getResponse();

            if (strpos($response->getStatusCode(), 2) !== 0) {
                throw new \Exception(sprintf('Received non OK HTTP status code from music.163.com: %d', $response->getStatusCode()));
            }

            throw new Music163Exception('Unknown error');
        }

        $body = (string)$res->getBody();

        if (empty($body)) {
            throw new Music163Exception('Received no body from music.163.com');
        }

        $json = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Music163Exception('Unable to decode JSON from music.163.com');
        }

        if (($json['code'] ?? null) !== 200) {
            throw new Music163Exception(sprintf('Received non OK code from music.163.com: %s', $json['code'] ?? 'unknown'));
        }

        return $json;
    }

    private function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client([
                RequestOptions::HEADERS => [
                    'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:84.0) Gecko/20100101 Firefox/84.0',
                    'Host' => 'music.163.com',
                    'Cache-Control' => 'max-age=0',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Connection' => 'keep-alive',
                ],
                RequestOptions::ALLOW_REDIRECTS => true,
            ]);
        }

        return $this->client;
    }
}