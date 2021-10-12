<?php

namespace App\Service;

use App\Document\Song;
use App\Entity\AudioWaveform;
use App\Exception\WaveformGenerationException;
use App\Repository\AudioWaveformRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use App\Exception\WaveformAlreadyExistsException;

class AudioWaveformGenerator
{
    /**
     * @var AudioWaveformRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(AudioWaveformRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->em = $entityManager;
    }

    public function generateWaveform(
        Song $song,
        string $localFile,
        string $outputFile,
        bool $force = false,
        bool $keepJson = false,
        OutputInterface $output = null
    ): AudioWaveform
    {
        $waveform = $this->repository->findOneBySourceId($song->getId());

        if ($waveform && !$force) {
            throw new WaveformAlreadyExistsException($waveform);
        }

        $process = Process::fromShellCommandline(
            sprintf(
                'audiowaveform -i "%s" -o "%s" --pixels-per-second 20 --bits 8',
                $localFile,
                $outputFile
            )
        );

        $process->setTimeout(60);
        $process->start();

        $outputLines = '';
        foreach ($process as $type => $data) {
            $outputLines .= $data;

            if ($output) {
                $output->write($data);
            }
        }

        $lines = new ArrayCollection(array_filter(explode("\n", $outputLines)));

        if ($lines->last() !== 'Done') {
            throw new WaveformGenerationException(
                sprintf(
                    'Unable to generate waveform from "%s".\n Command: "%s"\n Command Output: %s',
                    $localFile,
                    $process->getCommandLine(),
                    $outputLines
                )
            );
        }

        preg_match('/Output file: (.+$)/', $lines->get($lines->key() - 1), $matches);
        $writtenOutputFile = $matches[1] ?? null;

        if (!$writtenOutputFile) {
            throw new WaveformGenerationException(
                'Could not extract output json file path of waveform'
            );
        }

        $outputContent = file_get_contents($writtenOutputFile);

        if (!$outputContent) {
            throw new WaveformGenerationException(sprintf('Could not read waveform json from "%s"', $writtenOutputFile));
        }

        $json = json_decode($outputContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new WaveformGenerationException(sprintf('Unable to parse JSON: %s', json_last_error_msg()));
        }

        if (empty($json['data'])) {
            throw new WaveformGenerationException('Expected JSON to have key "data');
        }

        if (!$waveform instanceof AudioWaveform) {
            $waveform = new AudioWaveform();
            $waveform
                ->setCreated(new \DateTime())
                ->setSourceId($song->getId())
            ;

            $this->em->persist($waveform);
        }

        $waveform
            ->setWaveform($this->formatForWavesurfer($json['data']))
            ->setUpdated(new \DateTime())
        ;
        $this->em->flush();

        if (!$keepJson) {
            unlink($writtenOutputFile);
        }

        return $waveform;
    }

    /*
     * Copied python magicc from https://wavesurfer-js.org/faq/
     */
    private function formatForWavesurfer(array $data): array
    {
        $maxVal = max($data);

        $formatted = [];
        foreach ($data as $x) {
            $formatted[] = round($x / $maxVal, 2);
        }

        return $formatted;
    }
}