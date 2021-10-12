<?php

namespace App\Command;

use App\Entity\KodiImageType;
use App\Service\KodiImageImporter;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageImporterCommand extends Command
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $force = false;

    /**
     * @var KodiImageImporter
     */
    private $importer;

    public function __construct(LoggerInterface $logger, KodiImageImporter $importer)
    {
        parent::__construct('kodi:images:import');

        $this->logger = $logger;
        $this->importer = $importer;
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this->setName('kodi:images:import')
            ->setDescription('Import images from Kodi.')
            ->setHelp('Import either a specific type or all available image types from Kodi.')
        ;

        $this->addOption(
            'type',
            't',
            InputOption::VALUE_REQUIRED,
            'Specify an image type');

        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force import even if image already exists');

        $this->addOption(
            'list',
            'l',
            InputOption::VALUE_NONE,
            'List available image types');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->force = $input->getOption('force');

        if ($input->getOption('list')) {
            $types = [];
            foreach ($this->importer->getAvailableImageTypes() as $type) {
                $types[] = [
                    $type->getName(), $type->getPath(), $type->getWidth(), $type->getHeight(),
                ];
            }

            $table = new Table($output);
            $table
                ->setHeaders(['Name', 'Path', 'Width', 'Height'])
                ->setRows($types)
            ;
            $table->render();

            return 0;
        }

        /** @var KodiImageType[] $types */
        $types = [];
        if ($name = $input->getOption('type')) {
            $type = $this->importer->getImageTypeByName($name);

            if (!$type instanceof KodiImageType) {
                throw new EntityNotFoundException(sprintf('Image type with name "%s" not found', $name));
            }

            $types[] = $type;
        } else {
            $types = $this->importer->getAvailableImageTypes();
        }

        foreach ($types as $type) {
            switch ($type->getName()) {
                case KodiImageImporter::IMAGE_TYPE_ALBUM:
                case KodiImageImporter::IMAGE_TYPE_ALBUM_THUMB:
                    $this->importer->importAlbumImages($type);
                    break;

                case KodiImageImporter::IMAGE_TYPE_ARTIST_COVER:
                case KodiImageImporter::IMAGE_TYPE_ARTIST_COVER_THUMB:
                    $this->importer->importArtistCoverImages($type);
                    break;

                case KodiImageImporter::IMAGE_TYPE_ARTIST_FANART:
                case KodiImageImporter::IMAGE_TYPE_ARTIST_FANART_THUMB:
                    $this->importer->importArtistFanartImages($type);
                    break;

                default:
                    $this->logger->warning(sprintf('No import routine configured for "%s".', $type->getName()));
            }
        }

        return 0;
    }
}
