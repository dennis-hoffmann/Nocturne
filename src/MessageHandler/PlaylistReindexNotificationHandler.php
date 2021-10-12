<?php

namespace App\MessageHandler;

use App\Document\Manager\SongManager;
use App\Document\Object\PlaylistEntryObject;
use App\Document\Song;
use App\Message\PlaylistReindexNotification;
use App\Repository\PlaylistRepository;
use App\Service\PlaylistManager;
use App\Document\Playlist as ElasticPlaylist;
use Doctrine\Common\Collections\ArrayCollection;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PlaylistReindexNotificationHandler implements MessageHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PlaylistRepository
     */
    private $repository;

    /**
     * @var PlaylistManager
     */
    private $manager;

    /**
     * @var SongManager
     */
    private $songManager;

    /**
     * @var array
     */
    private $songCache;

    public function __construct(LoggerInterface $logger, PlaylistRepository $repository, PlaylistManager $manager, SongManager $songManager)
    {
        $this->logger = $logger;
        $this->repository = $repository;
        $this->manager = $manager;
        $this->songManager = $songManager;
    }

    public function __invoke(PlaylistReindexNotification $message)
    {
        $playlistId = $message->getId();
        $playlist = $this->repository->find($playlistId);

        $this->logger->info(sprintf('Indexing playlist %s (%d)', $playlist->getName(), $playlistId));

        $this->songCache = [];

        $document = new ElasticPlaylist();
        $document
            ->setName($playlist->getName())
            ->setId($playlistId)
            ->setUpdated(new \DateTime())
            ->setOwnerId($playlist->getOwner()->getId())
            // Todo
            //->setCreated($playlist->get)
        ;

        $entries = new ArrayCollection();

        foreach ($playlist->getEntries() as $entry) {
            $object = new PlaylistEntryObject();
            try {
                $song = $this->getSong($entry->getSongId());
            } catch (Missing404Exception $e) {
                // Ignore missing songs
                continue;
            }
            $object
                ->setId($entry->getId())
                ->setPosition($entry->getPosition())
                ->setTitle($song->getTitle())
                ->setSongId($entry->getSongId())
                ->setArtist($song->getArtist()->getName())
                ->setArtistId($song->getArtist()->getId())
                ->setAlbum($song->getAlbum())
                ->setAlbumId($song->getAlbumId())
                ->setThumbnail($song->getThumbnail())
                ->setAdded($entry->getAdded())
            ;

            $entries->set($entry->getPosition(), $object);
        }

        $document->setEntries($entries);

        $this->manager->getElastic()->persist($document);
        $this->manager->getElastic()->commit('flush');

        $this->logger->info('Successfully indexed playlist ' . $playlistId);

    }

    private function getSong(int $id): Song
    {
        if (empty($this->songCache[$id])) {
            $this->songCache[$id] = $this->songManager->getSongById($id);
        }

        return $this->songCache[$id];
    }
}