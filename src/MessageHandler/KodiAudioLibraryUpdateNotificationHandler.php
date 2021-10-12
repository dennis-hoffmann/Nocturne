<?php

namespace App\MessageHandler;

use App\Document\Manager\SongManager;
use App\Document\Song;
use App\Exception\EmptyImageSourceException;
use App\Exception\KodiImageAlreadyExistsException;
use App\Http\Kodi;
use App\Indexer\SongIndexer;
use App\Message\AudioWaveformGenerateNotification;
use App\Message\KodiAudioLibraryUpdateNotification;
use App\Service\KodiImageImporter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class KodiAudioLibraryUpdateNotificationHandler implements MessageHandlerInterface
{
    /**
     * @var SongManager
     */
    private $songManager;

    /**
     * @var KodiImageImporter
     */
    private $imageImporter;

    /**
     * @var Kodi
     */
    private $kodi;

    /**
     * @var SongIndexer
     */
    private $indexer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(
        SongManager $songManager,
        KodiImageImporter $imageImporter,
        Kodi $kodi,
        SongIndexer $indexer,
        LoggerInterface $logger,
        MessageBusInterface $bus
    ) {
        $this->songManager = $songManager;
        $this->imageImporter = $imageImporter;
        $this->kodi = $kodi;
        $this->indexer = $indexer;
        $this->logger = $logger;
        $this->bus = $bus;
    }

    public function __invoke(KodiAudioLibraryUpdateNotification $message)
    {
        $remoteSongData = $this->kodi->getSongDetails($message->getId());
        $remoteArtistData = $this->kodi->getArtistDetails($remoteSongData['albumartistid'][0] ?? $remoteSongData['artistid'][0]);

        $existing = $this->songManager->getSongByTitleAlbumAndArtist(
            $remoteSongData['title'],
            $remoteSongData['album'],
            $remoteArtistData['label']
        );

        if ($existing->count() && !$message->isForce()) {
            /** @var Song $song */
            $song = $existing->first();
            $info = sprintf(
                'Skipping index update for %s (%s by %s). Entry already exists (%s)',
                $message->getId(),
                $song->getTitle(),
                $song->getArtistname(),
                $song->getId()
            );

            $this->logger->info($info);
            $this->dispatchMercureUpdate(
                sprintf('%s by %s', $song->getTitle(), $song->getArtistname()),
                $info,
                $song->getThumbnail()
            );

            return;
        }

        foreach ([KodiImageImporter::IMAGE_TYPE_ALBUM, KodiImageImporter::IMAGE_TYPE_ALBUM_THUMB] as $typeName) {
            $type = $this->imageImporter->getImageTypeByName($typeName);

            try {
                $path = $this->imageImporter->getBasePath() . $type->getPath();
                $this->imageImporter->createImageFromUrl($this->imageImporter->getKodiImageLink($remoteSongData['thumbnail']), $remoteSongData['title'], $remoteSongData['albumid'], $type, $path);
            } catch (KodiImageAlreadyExistsException $e) {
            } catch (EmptyImageSourceException $e) {
            }
        }

        foreach ([KodiImageImporter::IMAGE_TYPE_ARTIST_COVER, KodiImageImporter::IMAGE_TYPE_ARTIST_COVER_THUMB] as $typeName) {
            $type = $this->imageImporter->getImageTypeByName($typeName);

            try {
                $path = $this->imageImporter->getBasePath() . $type->getPath();
                $this->imageImporter->createImageFromUrl($this->imageImporter->getKodiImageLink($remoteArtistData['thumbnail']), $remoteArtistData['label'], $remoteArtistData['artistid'], $type, $path);
            } catch (KodiImageAlreadyExistsException $e) {
            } catch (EmptyImageSourceException $e) {
            }
        }

        foreach ([KodiImageImporter::IMAGE_TYPE_ARTIST_FANART, KodiImageImporter::IMAGE_TYPE_ARTIST_FANART_THUMB] as $typeName) {
            $type = $this->imageImporter->getImageTypeByName($typeName);

            try {
                $path = $this->imageImporter->getBasePath() . $type->getPath();
                $this->imageImporter->createImageFromUrl($this->imageImporter->getKodiImageLink($remoteArtistData['fanart']), $remoteArtistData['label'], $remoteArtistData['artistid'], $type, $path);
            } catch (KodiImageAlreadyExistsException $e) {
            } catch (EmptyImageSourceException $e) {
            }
        }

        $this->imageImporter->flushImages();

        unset($remoteSongData['playount']);

        $artist = $this->indexer->createArtistObjectFromKodi($remoteArtistData);
        $song = $this->indexer->createSongDocumentFromKodi($remoteSongData, $artist);

        $this->songManager->getSongManager()->persist($song);
        $this->songManager->getSongManager()->commit('flush');

        $this->bus->dispatch(new AudioWaveformGenerateNotification($song->getId()));

        $info = sprintf('Index Update for ID %s - %s by %s', $song->getId(), $song->getTitle(), $song->getArtistname());
        $this->logger->info($info);

        $this->dispatchMercureUpdate(
            sprintf('%s by %s', $song->getTitle(), $song->getArtistname()),
            $info,
            $song->getThumbnail()
        );
    }

    private function dispatchMercureUpdate(string $headline, string $info, ?string $thumbnail)
    {
        $this->bus->dispatch(new Update('audio_update', json_encode([
            'event' => 'audio_update',
            'data'  => [
                'message' => $info,
                'headline' => $headline,
                'icon' => $thumbnail,
            ],
        ])));
    }
}