<?php

namespace App\Command;

use App\Document\Manager\SongManager;
use App\Filter\LocalFilePathFilter;
use App\Http\Kodi;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SubstituteAudioFilePathCommand extends Command
{
    /**
     * @var LocalFilePathFilter
     */
    private $localFilePathFilter;

    /**
     * @var SongManager
     */
    private $songManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Kodi
     */
    private $kodi;

    public function __construct(
        LocalFilePathFilter $localFilePathFilter,
        SongManager $songManager,
        LoggerInterface $logger,
        Kodi $kodi
    ) {
        parent::__construct('app:kodi:substitute:audio');

        $this->localFilePathFilter = $localFilePathFilter;
        $this->songManager = $songManager;
        $this->logger = $logger;
        $this->kodi = $kodi;
    }

    protected function configure()
    {
        $this->addOption(
            'reset',
            'r',
            InputOption::VALUE_NONE,
            'Reset file path to its initial value.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesCount = 0;
        $songManager = $this->songManager->getSongManager();
        $search = $songManager->createSearch();
        $reset = $input->getOption('reset');

        $search->addQuery(new MatchAllQuery())
            ->setScroll()
            ->setSize(25)
            ->addUriParam('_source', [
                'id', 'file', 'artistname', 'title'
            ])
        ;

        $docs = $songManager->findRaw($search);

        if ($reset) {
            $this->logger->info(sprintf('Resetting value of %d songs', $docs->count()));
        }

        do {
            $currentLink = $docs->current()['_source']['file'];
            $needsReplacement = $this->localFilePathFilter->needsReplacing($currentLink);

            if ($needsReplacement === false) {
                $this->logger->warning(sprintf(
                    'Unable to match regex pattern "%s" to file Link "%s". %s by %s (%s)',
                    $this->localFilePathFilter->getPattern(),
                    $currentLink,
                    $docs->current()['_source']['title'],
                    $docs->current()['_source']['artistname'],
                    $docs->current()['_id']
                ));

                continue;
            }

            if ($reset) {
                $songManager->update($docs->current()['_id'], ['playable_file' => $this->kodi->kodiFile($currentLink)]);
            } else if ($needsReplacement === true) {
                $songManager->update($docs->current()['_id'], ['playable_file' => $this->localFilePathFilter->filter($currentLink)]);
            } else {
                continue;
            }

            $filesCount++;
            $this->logger->debug(sprintf(
                'Updated file link by %s by %s (%s)',
                $docs->current()['_source']['title'],
                $docs->current()['_source']['artistname'],
                $docs->current()['_id']
            ));
        } while ($docs->next() && $docs->valid());

        $this->logger->info(sprintf('Updated %d files.', $filesCount));

        return 0;
    }
}
