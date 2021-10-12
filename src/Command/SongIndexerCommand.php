<?php

namespace App\Command;

use App\Document\Manager\SongManager;
use App\Document\Object\ArtistObject;
use App\Document\Song;
use App\Http\Kodi;
use App\Indexer\SongIndexer;
use App\Message\AudioWaveformGenerateNotification;
use App\Message\KodiAudioLibraryUpdateNotification;
use App\Repository\AudioWaveformRepository;
use App\Repository\KodiImageRepository;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SongIndexerCommand extends Command
{
    const CHUNK_SIZE = 100;
    const THUMBNAIL_IMAGE_TYPE_NAME = 'album';

    /**
     * @var Kodi
     */
    private $kodi;

    /**
     * @var SongManager
     */
    private $songManager;

    /**
     * @var array
     */
    private $buffer = [];

    /**
     * @var ArtistObject[]
     */
    private $artists = [];

    /**
     * @var ArtistObject
     */
    private $fallbackArtist;

    /**
     * @var KodiImageRepository
     */
    private $imageRepository;

    /**
     * @var SongIndexer
     */
    private $indexer;

    /**
     * @var AudioWaveformRepository
     */
    private $waveformRepository;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(
        SongIndexer $indexer,
        Kodi $kodi,
        SongManager $songManager,
        KodiImageRepository $imageRepository,
        AudioWaveformRepository $waveformRepository,
        MessageBusInterface $bus
    ) {
        parent::__construct('kodi:index:songs');

        $this->kodi = $kodi;
        $this->songManager = $songManager;
        $this->imageRepository = $imageRepository;
        $this->indexer = $indexer;
        $this->waveformRepository = $waveformRepository;
        $this->bus = $bus;
    }

    protected function configure()
    {
        $this->addOption(
            'full',
            'f',
            InputOption::VALUE_NONE,
            'Rebuild complete index'
        );

        $this->addOption(
            'songid',
            's',
            InputOption::VALUE_REQUIRED,
            'Queue specified song id'
        );

        $this->addOption(
            'albumid',
            'a',
            InputOption::VALUE_REQUIRED,
            'Queue all songs of specified album id'
        );
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($songId = $input->getOption('songid')) {
            $this->bus->dispatch(new KodiAudioLibraryUpdateNotification($songId, true));
        }

        if ($albumId = $input->getOption('albumid')) {
            $songs = $this->songManager->getSongsByAlbumId($albumId);

            foreach ($songs as $song) {
                $this->bus->dispatch(new KodiAudioLibraryUpdateNotification($song->getId(), true));
            }
        }

        if ($songId || $albumId) {
            $output->writeln('Finished');

            return 0;
        }

        $fetchNo = 0;
        $start = 0;
        $end = self::CHUNK_SIZE;
        $chunk = self::CHUNK_SIZE;

        $output->writeln('<info>Fetching artists...</info>');
        $this->fetchArtists();

        // Fetch songs to buffer
        do {
            $songs = $this->kodi->getSongs($start, $end);

            $total = $songs['limits']['total'] ?? 0;
            $remaining = $total - $end;
            $start += $chunk;
            $fetchNo++;

            $output->writeln(
                sprintf(
                    "<info>Fetch no. %d\tChunk size %d\tRemaining: %d\tTotal size: %d</info>",
                    $fetchNo,
                    $chunk,
                    $remaining < 0 ? 0 : $remaining,
                    $total
                )
            );

            $end += min($remaining, $chunk);

            $this->buffer = array_merge($this->buffer, $songs['songs']);
        } while ($remaining > 0);

        $this->indexSongs($output, $input->getOption('full'));

        return 0;
    }

    private function fetchArtists()
    {
        $this->fallbackArtist = new ArtistObject();
        $this->fallbackArtist
            ->setName('Unkown Artist')
            ->setId(999999)
        ;

        foreach ($this->kodi->getArtists()['artists'] as $artist) {
            $this->artists[$artist['artistid']] = $this->indexer->createArtistObjectFromKodi($artist);
        }
    }

    private function indexSongs(OutputInterface $output, bool $full = false)
    {
        if ($full) {
            $output->writeln('<comment>Clearing elastic index.</comment>');
            $this->songManager->getSongManager()->dropAndCreateIndex();

            $output->writeln(sprintf('<info>Creating %d songs.</info>', count($this->buffer)));
        } else {
            $output->writeln('<info>Creating only new songs and updating old ones</info>');
        }

        foreach ($this->buffer as $song) {
            $existingSong = false;
            $artist = $this->artists[$song['albumartistid'][0]] ?? $this->fallbackArtist;

            try {
                /** @var Song|null $existingSong */
                $existingSong = $this->songManager->getSongById($song['songid']);
            } catch (Missing404Exception $e) {}

            if (!$full && $existingSong instanceof Song) {
                $changed = false;

                $imageLink = $this->indexer->getAlbumImageLink($existingSong->getAlbumId());
                $playableFile = $this->kodi->kodiFile($song['file']);

                if ($imageLink !== $existingSong->getThumbnail()) {
                    $output->writeln(sprintf('Updating album cover of %s by %s', $existingSong->getTitle(), $existingSong->getArtistname()));
                    $existingSong->setThumbnail($imageLink);
                    $changed = true;
                }

                if ($existingSong->getPlayableFile() !== $playableFile) {
                    $output->writeln(sprintf('Updating file of %s by %s', $existingSong->getTitle(), $existingSong->getArtistname()));
                    $existingSong->setPlayableFile($playableFile);
                }

                if (
                    $existingSong->getArtistname() !== $artist->getName()
                    || $existingSong->getArtistid() !== $artist->getId()
                    || $existingSong->getArtist()->getThumbnail() !== $artist->getThumbnail()
                    || $existingSong->getArtist()->getFanart() !== $artist->getFanart()
                ) {
                    $output->writeln(sprintf('Updating artist of %s by %s', $existingSong->getTitle(), $existingSong->getArtistname()));
                    $existingSong->setArtist($artist);
                    $existingSong->setArtistname($artist->getName());
                    $existingSong->setArtistid($artist->getId());
                    $changed = true;
                }

                $waveformExists = $this->waveformRepository->waveformExists($existingSong->getId());

                if (empty($existingSong->getWaveform()) && $waveformExists) {
                    $output->writeln(sprintf('Updating waveform of %s by %s', $existingSong->getTitle(), $existingSong->getArtistname()));

                    $waveform = $this->waveformRepository->findOneBySourceId($existingSong->getId());
                    $existingSong->setWaveform(json_encode($waveform->getWaveform()));

                    $this->songManager->getSongManager()->persist($existingSong);
                    $this->songManager->getSongManager()->commit('flush');

                    continue;
                }

                if ($changed) {
                    $this->songManager->getSongManager()->persist($existingSong);
                    $this->songManager->getSongManager()->commit('flush');
                }

                continue;
            }

            $songObj = $this->indexer->createSongDocumentFromKodi($song, $artist);

            if (!$full || $output->isVerbose()) {
                $output->writeln(sprintf('Created %s by %s', $songObj->getTitle(), $songObj->getArtistname()));
            }

            $this->songManager->getSongManager()->persist($songObj);
            $this->songManager->getSongManager()->commit();

            $this->bus->dispatch(new AudioWaveformGenerateNotification($songObj->getId()));
        }

        $output->writeln('<info>Finished</info>');
    }
}