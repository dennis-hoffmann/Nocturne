<?php

namespace App\Command;

use App\Document\Manager\SongManager;
use App\Message\AudioWaveformGenerateNotification;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AudioWaveformQueueCommand extends Command
{
    /**
     * @var SongManager
     */
    private $songManager;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(SongManager $songManager, MessageBusInterface $bus)
    {
        parent::__construct('app:audiowaveform:queue');

        $this->songManager = $songManager;
        $this->bus = $bus;
    }

    protected function configure()
    {
        $this->setDescription('Queue waveform generation for songs with missing data');

        $this->addOption(
            'songid',
            's',
            InputOption::VALUE_REQUIRED,
            'Only queue specific song ID'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($songId = $input->getOption('songid')) {
            try {
                $song = $this->songManager->getSongById($songId);
            } catch (Missing404Exception $e) {
                $output->writeln(sprintf('Song by ID %s not found', $songId));

                return 1;
            }

            $output->writeln(sprintf(
                'Queueing %s by %s (%s)',
                $song->getTitle(),
                $song->getArtistname(),
                $song->getId()
            ));

            $this->bus->dispatch(new AudioWaveformGenerateNotification($song->getId()));

            return 0;
        }

        $search = $this
            ->songManager
            ->getSongManager()
            ->createSearch()
            ->setScroll()
            ->setSize(25)
            ->addUriParam('_source', [
                'id', 'title', 'artistname'
            ])
        ;

        $docs = $this->songManager->getSongManager()->findRaw($search);

        do {
            $output->writeln(sprintf(
                'Queueing %s by %s (%s)',
                $docs->current()['_source']['title'],
                $docs->current()['_source']['artistname'],
                $docs->current()['_id']
            ));

            $this->bus->dispatch(new AudioWaveformGenerateNotification($docs->current()['_id']));
        } while ($docs->next() && $docs->valid());
    }
}