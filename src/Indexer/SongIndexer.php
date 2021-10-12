<?php

namespace App\Indexer;

use App\Document\Object\ArtistObject;
use App\Document\Song;
use App\Entity\KodiImage;
use App\Filter\LocalFilePathFilter;
use App\Http\Kodi;
use App\Repository\KodiImageRepository;
use App\Service\KodiImageImporter;

class SongIndexer
{
    /**
     * @var KodiImageRepository
     */
    private $imageRepository;

    /**
     * @var Kodi
     */
    private $kodi;

    /**
     * @var LocalFilePathFilter
     */
    private $localFilePathFilter;

    public function __construct(KodiImageRepository $imageRepository, Kodi $kodi, LocalFilePathFilter $localFilePathFilter)
    {
        $this->imageRepository = $imageRepository;
        $this->kodi = $kodi;
        $this->localFilePathFilter = $localFilePathFilter;
    }

    public function createArtistObjectFromKodi(array $data): ArtistObject
    {
        $artistObj = new ArtistObject();

        $artistObj
            ->setName($data['artist'])
            ->setId($data['artistid'])
            ->setBorn($data['born'])
            ->setDateadded(new \DateTime($data['dateadded']) ?? null)
            ->setDescription($data['description'])
            ->setDied($data['died'])
            ->setDisbanded($data['disbanded'])
            ->setFanart($this->getArtistFanartLink($data['artistid']) ?? $this->kodi->createFilesystemProxyLink($data['fanart']))
            ->setFormed($data['formed'])
            ->setGenre($data['genre'])
            ->setInstrument($data['instrument'])
            ->setIsalbumartist($data['isalbumartist'])
            ->setLabel($data['label'])
            ->setMood($data['mood'])
            ->setMusicbrainzartistid($data['musicbrainzartistid'])
            ->setSonggenres(array_filter(array_map(
                    function ($genre) {
                        return $genre['title'] ?? null;
                    },
                    $data['songgenres'] ?? []
                ))
            )
            ->setStyle($data['style'])
            ->setThumbnail($this->getArtistImageLink($data['artistid']) ?? $this->kodi->createFilesystemProxyLink($data['thumbnail']))
            ->setYearsactive($data['yearsactive'])
        ;

        return $artistObj;
    }

    public function createSongDocumentFromKodi(array $data, ArtistObject $artist): Song
    {
        $songObj = new Song();

        $songObj
            ->setAdded(new \DateTime($data['dateadded']))
            ->setAlbum($data['album'])
            ->setAlbumId($data['albumid'])
            ->setArtist($artist)
            ->setArtistname($artist->getName())
            ->setArtistid($artist->getId())
            ->setFile($data['file'])
            ->setPlayableFile($this->localFilePathFilter->filter($data['file']))
            ->setId($data['songid'])
            ->setLastPlayed(empty($data['lastplayed']) ? null : new \DateTime($data['lastplayed']))
            ->setLength($data['duration'])
            ->setMood($data['mood'][0] ?? null)
            ->setThumbnail($this->getAlbumImageLink($data['albumid']) ?? $this->kodi->createFilesystemProxyLink($data['thumbnail']))
            ->setTitle($data['label'])
            ->setTrack($data['track'])
            ->setUserRating($data['userrating'])
            ->setVotes($data['votes'])
            ->setYear($data['year'])
        ;

        if ($data['playcount']) {
            $songObj->setPlayCount($data['playcount']);
        }

        foreach ($data['genre'] as $genre) {
            $songObj->addGenre($genre);
        }

        return $songObj;
    }

    public function getArtistFanartLink(int $artistId)
    {
        $image = $this->imageRepository->findOneByTargetIdAndType($artistId, KodiImageImporter::IMAGE_TYPE_ARTIST_FANART);

        if ($image instanceof KodiImage) {
            return '/images' . $image->getImageType()->getPath() . $image->getFilename();
        }

        return null;
    }

    public function getArtistImageLink(int $artistId)
    {
        $image = $this->imageRepository->findOneByTargetIdAndType($artistId, KodiImageImporter::IMAGE_TYPE_ARTIST_COVER);

        if ($image instanceof KodiImage) {
            return '/images' . $image->getImageType()->getPath() . $image->getFilename();
        }

        return null;
    }

    public function getAlbumImageLink(int $albumId)
    {
        $image = $this->imageRepository->findOneByTargetIdAndType($albumId, KodiImageImporter::IMAGE_TYPE_ALBUM);

        if ($image instanceof KodiImage) {
            return '/images' . $image->getImageType()->getPath() . $image->getFilename();
        }

        return null;
    }
}