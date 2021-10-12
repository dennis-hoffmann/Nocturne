<?php

namespace App\Service;

use App\Document\Manager\AlbumManager;
use App\Document\Manager\ArtistManager;
use App\Entity\KodiImage;
use App\Entity\KodiImageType;
use App\Exception\EmptyImageSourceException;
use App\Exception\KodiImageAlreadyExistsException;
use App\Http\Kodi;
use App\Repository\KodiImageRepository;
use App\Repository\KodiImageTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class KodiImageImporter
{
    const IMAGE_TYPE_ALBUM = 'album';
    const IMAGE_TYPE_ALBUM_THUMB = 'album_thumb';

    const IMAGE_TYPE_ARTIST_COVER = 'artist_cover';
    const IMAGE_TYPE_ARTIST_COVER_THUMB = 'artist_cover_thumb';

    const IMAGE_TYPE_ARTIST_FANART = 'artist_fanart';
    const IMAGE_TYPE_ARTIST_FANART_THUMB = 'artist_fanart_thumb';

    /**
     * @var Kodi
     */
    private $kodi;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var AlbumManager
     */
    private $albumManager;

    /**
     * @var ArtistManager
     */
    private $artistManager;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var KodiImageRepository
     */
    private $kodiImageRepo;

    /**
     * @var KodiImageTypeRepository
     */
    private $kodiImageTypeRepo;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $force = false;

    /**
     * @var array|null
     */
    private $albums;

    /**
     * @var array|null
     */
    private $artists;

    public function __construct(
        Kodi $kodi,
        ContainerInterface $container,
        AlbumManager $albumManager,
        ArtistManager $artistManager,
        EntityManagerInterface $entityManager,
        KodiImageRepository $kodiImageRepo,
        KodiImageTypeRepository $kodiImageTypeRepo,
        LoggerInterface $logger
    )
    {
        $this->kodi = $kodi;
        $this->container = $container;
        $this->artistManager = $artistManager;
        $this->em = $entityManager;
        $this->kodiImageRepo = $kodiImageRepo;
        $this->kodiImageTypeRepo = $kodiImageTypeRepo;
        $this->logger = $logger;
        $this->albumManager = $albumManager;
    }

    public function importAlbumImages(KodiImageType $type)
    {
        $this->logger->info(sprintf('Using import routine for %s', $type->getName()));

        $path = $this->getBasePath() . $type->getPath();
        $this->createOutputDir($path);

        $written = 0;
        foreach ($this->getAlbums() as $album) {
            try {
                $this->createImageFromUrl($this->getKodiImageLink($album['thumbnail']), $album['title'], $album['id'], $type, $path);
            } catch (\Exception $e) {
                continue;
            }

            $written++;
        }

        $this->logger->info(sprintf('Saving %d images to DB.', $written));
        $this->em->flush();
    }

    public function importArtistCoverImages(KodiImageType $type)
    {
        $this->logger->info(sprintf('Using import routine for %s', $type->getName()));

        $path = $this->getBasePath() . $type->getPath();
        $this->createOutputDir($path);

        $written = 0;
        foreach ($this->getArtists() as &$artist) {
            try {
                $this->createImageFromUrl($this->getKodiImageLink($artist['thumbnail']), $artist['name'], $artist['id'], $type, $path);
            } catch (\Exception $e) {
                continue;
            }

            $written++;
        }

        $this->logger->info(sprintf('Saving %d images to DB.', $written));
        $this->em->flush();
    }

    public function importArtistFanartImages(KodiImageType $type)
    {
        $this->logger->info(sprintf('Using import routine for %s', $type->getName()));

        $path = $this->getBasePath() . $type->getPath();
        $this->createOutputDir($path);

        $written = 0;
        foreach ($this->getArtists() as &$artist) {
            try {
                $this->createImageFromUrl($this->getKodiImageLink($artist['fanart']), $artist['name'], $artist['id'], $type, $path);
            } catch (\Exception $e) {
                continue;
            }

            $written++;
        }

        $this->logger->info(sprintf('Saving %d images to DB.', $written));
        $this->em->flush();
    }


    /**
     * @param string $url
     * @param string $name
     * @param int $identifier
     * @param KodiImageType $type
     * @param string $path
     * @param int $version
     *
     * @return KodiImage
     * @throws EmptyImageSourceException
     * @throws KodiImageAlreadyExistsException
     * @throws \ImagickException
     */
    public function createImageFromUrl(?string $url, string $name, int $identifier, KodiImageType $type, string $path, int $version = null): KodiImage
    {
        $kodiImage = $this
            ->kodiImageRepo
            ->findOneByTargetIdAndType($identifier, $type->getName(), $version)
        ;

        if (
            !$this->force
            && $kodiImage instanceof KodiImage
        ) {
            $this->logger->debug(sprintf('Skipping import for image type %s and entry %s', $type->getName(), $identifier));

            throw new KodiImageAlreadyExistsException($kodiImage);
        }

        if (empty($url)) {
            $this->logger->debug(sprintf('Skipping import of image type "%s" for "%s" (%s). Empty value.', $type->getName(), $name, $identifier));

            throw new EmptyImageSourceException();
        }

        try {
            $file = file_get_contents($url);
        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                'Failed to fetch image for "%s" (%s). %s',
                $name,
                $identifier,
                $e->getMessage()
            ));

            throw $e;
        }

        try {
            $img = new \Imagick();
            $img->readImageBlob($file);
        }
        catch (\ImagickException $e) {
            // Zero size image exception
            if ($e->getCode() === 1) {
                throw new EmptyImageSourceException();
            }
        }
        catch (\Exception $e) {
            $this->logger->error(sprintf(
                'Failed to read image blob for "%s" (%s). %s',
                $name,
                $identifier,
                $e->getMessage()
            ));

            throw $e;
        }

        $img->setImageCompressionQuality(75);
        $img->adaptiveResizeImage($type->getWidth(), 0);
        $filename = sprintf('%s_%s%s.%s', $type->getName(), $identifier, ($version ? ('_v' . $version) : ''), $this->getImageExtension($img));
//        $filename = $type->getName() . '_' . $identifier . $version ? ('_v' . $version) : '' . $this->getImageExtension($img);
        $img->writeImage(rtrim($path, '/') . '/' . $filename);

        if (!$kodiImage instanceof KodiImage) {
            $kodiImage = new KodiImage();
            $kodiImage
                ->setTargetId($identifier)
                ->setImageType($type)
                ->setVersion($version ?: 1)
                ->setCreated(new \DateTime())
            ;

            $this->em->persist($kodiImage);
        }

        $kodiImage
            ->setSourceUrl($url)
            ->setFilename($filename)
            ->setUpdated(new \DateTime())
        ;

        $this->logger->info(sprintf(
            'Wrote image type %s for %s (%s)',
            $type->getName(),
            $name,
            $identifier
        ));

        return $kodiImage;
    }

    public function deleteImage(KodiImage $image)
    {
        $filePathName = $this->getBasePath() . $image->getImageType()->getPath() . $image->getFilename();

        if (!is_file($filePathName)) {
            return false;
        }

        if (!unlink($filePathName)) {
            return false;
        }

        $this->em->remove($image);
        $this->em->flush($image);

        return true;
    }

    public function flushImages()
    {
        $this->em->flush();
    }

    public function getKodiImageLink($url)
    {
        if (strpos($url, 'http') === false) {
            $url = $this->kodi->createFilesystemProxyLink($url, true);
        } else if (strpos($url, $this->kodi->getKodiBaseUrl()) === 0) {
            $imagePath = str_replace($this->kodi->getKodiBaseUrl() . '/image/', '', rawurldecode($url));
            $url = $this->kodi->createFilesystemProxyLink($imagePath, true);
        }

        return $url;
    }

    /**
     * @return KodiImageType[]
     */
    public function getAvailableImageTypes(): array
    {
        return $this->kodiImageTypeRepo->findAll();
    }

    public function getImageTypeByName(string $name): ?KodiImageType
    {
        return $this->kodiImageTypeRepo->findOneBy([
            'name' => $name,
        ]);
    }

    public function getBasePath(): string
    {
        return $this->container->getParameter('kernel.project_dir') . '/public/images';
    }

    private function getAlbums()
    {
        if ($this->albums === null) {
            $this->albums = $this->albumManager->getAlbums();
        }

        return $this->albums;
    }

    private function getArtists()
    {
        if ($this->artists === null) {
            $this->artists = $this->artistManager->getArtists();
        }

        return $this->artists;
    }

    private function getImageExtension(\Imagick $img)
    {
        switch ($img->getImageFormat()) {
            case 'JPEG':
                return 'jpg';
            case 'PNG':
                return 'png';
            default:
                return 'jpg';
        }
    }

    private function createOutputDir(string $path)
    {
        $fs = new Filesystem();
        $fs->mkdir($path);
    }
}
