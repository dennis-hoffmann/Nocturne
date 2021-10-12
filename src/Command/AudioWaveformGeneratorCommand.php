<?php

namespace App\Command;

use App\Document\Manager\SongManager;
use App\Http\Kodi;
use App\Service\AudioWaveformGenerator;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;

class AudioWaveformGeneratorCommand extends Command
{
    /**
     * @var SongManager
     */
    private $songManager;

    /**
     * @var AudioWaveformGenerator
     */
    private $waveformGenerator;

    /**
     * @var Kodi
     */
    private $kodi;

    /**
     * @var Container
     */
    private $container;

    public function __construct(SongManager $songManager, AudioWaveformGenerator $waveformGenerator, Kodi $kodi, ContainerInterface $container)
    {
        parent::__construct('app:audiowaveform:generate');

        $this->songManager = $songManager;
        $this->waveformGenerator = $waveformGenerator;
        $this->kodi = $kodi;
        $this->container = $container;
    }

    protected function configure()
    {
        $this->addArgument(
            'songid',
            InputArgument::REQUIRED,
            'Song ID to generate wave form for'
        );

        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force generating new waveform even when one exists'
        );

        $this->addOption(
            'keep-cache',
            'k',
            InputOption::VALUE_NONE,
            'Keep cache files of downloaded remote audio files'
        );

        $this->addOption(
            'keep-json',
            'j',
            InputOption::VALUE_NONE,
            'Keep generated json from audiowaveform command'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $songId = $input->getArgument('songid');
        $song = $this->songManager->getSongById($songId);

        if (!$song) {
            throw new Missing404Exception(sprintf('Song by ID %s not found', $songId));
        }

        $output->writeln(sprintf(
            'Generating wave form for %s by %s (%s)',
            $song->getTitle(),
            $song->getArtistname(),
            $song->getId()
        ));

        $outputFile = sprintf('%s/%s', $this->getCacheDir(), $song->getId() . '.json');

        if (!$this->useLocalFiles()) {
            $inputFile = sprintf('%s/%s', $this->getCacheDir(), basename($song->getFile()));

            $this->kodi->downloadSong($song, $inputFile);
        } else {
            $inputFile = $this->getPublicDir() . ltrim($song->getPlayableFile(), '/');
        }


        $waveform = $this->waveformGenerator->generateWaveform(
            $song,
            $inputFile,
            $outputFile,
            $input->getOption('force'),
            $input->getOption('keep-json'),
            $output
        );

        $song->setWaveform(json_encode($waveform->getWaveform()));

        $this->songManager->getSongManager()->persist($song);
        $this->songManager->getSongManager()->commit();

        if (!$this->useLocalFiles() && !$input->getOption('keep-cache')) {
            unlink($inputFile);
        }

        return 0;
    }

    private function getCacheDir(): string
    {
        return $this->container->getParameter('kernel.cache_dir');
    }

    private function useLocalFiles(): bool
    {
        return $this->container->getParameter('song.indexer.enable_locale_file_filter');
    }

    private function getPublicDir(): string
    {
        return $this->container->getParameter('kernel.project_dir') . '/public/';
    }
}