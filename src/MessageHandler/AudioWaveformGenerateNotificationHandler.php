<?php

namespace App\MessageHandler;

use App\Document\Manager\SongManager;
use App\Exception\WaveformAlreadyExistsException;
use App\Exception\WaveformGenerationException;
use App\Http\Kodi;
use App\Message\AudioWaveformGenerateNotification;
use App\Service\AudioWaveformGenerator;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AudioWaveformGenerateNotificationHandler implements MessageHandlerInterface
{
    /**
     * @var AudioWaveformGenerator
     */
    private $waveformGenerator;

    /**
     * @var SongManager
     */
    private $songManager;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Kodi
     */
    private $kodi;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        AudioWaveformGenerator $waveformGenerator,
        SongManager $songManager,
        ContainerInterface $container,
        Kodi $kodi,
        LoggerInterface $logger
    )
    {
        $this->waveformGenerator = $waveformGenerator;
        $this->songManager = $songManager;
        $this->container = $container;
        $this->kodi = $kodi;
        $this->logger = $logger;
    }

    public function __invoke(AudioWaveformGenerateNotification $message)
    {
        $song = $this->songManager->getSongById($message->getSongId());

        if (!$song) {
            throw new Missing404Exception(sprintf('Song by ID %s not found', $message->getSongId()));
        }

        $this->logger->info(sprintf(
            'Generating audio waveform for %s by %s (%s)',
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

        try  {
            $waveform = $this->waveformGenerator->generateWaveform(
                $song,
                $inputFile,
                $outputFile
            );
        } catch (WaveformGenerationException $e) {
            // delete files in case generation was not successful
            if (is_file($inputFile)) {
                unlink($inputFile);
            }

            if (is_file($outputFile)) {
                unlink($outputFile);
            }

            throw $e;
        }
        catch (WaveformAlreadyExistsException $e) {
            $waveform = $e->getWaveform();
            $this->logger->info(sprintf(
                'Using already existing waveform with ID %d last updated on %s',
                $waveform->getId(),
                $waveform->getUpdated()->format('Y-m-d H:i:s')
            ));
        }

        $song->setWaveform(json_encode($waveform->getWaveform()));

        $this->songManager->getSongManager()->persist($song);
        $this->songManager->getSongManager()->commit('flush');

        if (!$this->useLocalFiles()) {
            unlink($inputFile);
        }
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