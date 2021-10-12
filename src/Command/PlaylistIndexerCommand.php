<?php

namespace App\Command;

use App\Entity\Playlist;
use App\Message\PlaylistReindexNotification;
use App\Service\PlaylistManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PlaylistIndexerCommand extends Command
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var PlaylistManager
     */
    private $manager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Playlist[]
     */
    private $playlists = [];

    public function __construct(MessageBusInterface $bus, PlaylistManager $manager, LoggerInterface $logger)
    {
        parent::__construct('app:playlists:reindex');

        $this->bus = $bus;
        $this->manager = $manager;
        $this->logger = $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->notice('Dropping elastic index');
        $this->manager->getElastic()->dropAndCreateIndex();

        $this->playlists = $this->manager->getPlaylists();

        $this->logger->info(sprintf('Adding %d playlists to queue', count($this->playlists)));

        foreach ($this->playlists as $playlist) {
            $this->logger->info(sprintf(
                'Adding playlist "%s" (%d) of user "%s" (%d) to queue',
                $playlist->getName(),
                $playlist->getId(),
                $playlist->getOwner()->getEmail(),
                $playlist->getOwner()->getId())
            );

            $this->bus->dispatch(new PlaylistReindexNotification($playlist->getId()));
        }

        return 0;
    }
}