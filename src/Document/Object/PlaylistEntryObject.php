<?php

namespace App\Document\Object;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * @ES\NestedType()
 */
class PlaylistEntryObject
{
    /**
     * @ES\Id()
     *
     * @var integer|null
     */
    private $id;

    /**
     * @ES\Property(name="position", type="integer")
     *
     * @var int|null
     */
    private $position;

    /**
     * @ES\Property(name="artist", type="keyword")
     *
     * @var string|null
     */
    private $artist;

    /**
     * @ES\Property(name="artist_id", type="integer")
     *
     * @var int|null
     */
    private $artistId;

    /**
     * @ES\Property(name="title", type="keyword")
     *
     * @var string|null
     */
    private $title;

    /**
     * @ES\Property(name="song_id", type="integer")
     *
     * @var int|null
     */
    private $songId;

    /**
     * @ES\Property(name="album", type="keyword")
     *
     * @var string|null
     */
    private $album;

    /**
     * @ES\Property(name="album_id", type="integer")
     *
     * @var int|null
     */
    private $albumId;

    /**
     * @ES\Property(name="thumbnail", type="keyword")
     *
     * @var string|null
     */
    private $thumbnail;

    /**
     * @ES\Property(name="added", type="date")
     *
     * @var \DateTime|null
     */
    private $added;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     *
     * @return PlaylistEntryObject
     */
    public function setId(?int $id): PlaylistEntryObject
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int|null $position
     *
     * @return PlaylistEntryObject
     */
    public function setPosition(?int $position): PlaylistEntryObject
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getArtist(): ?string
    {
        return $this->artist;
    }

    /**
     * @param null|string $artist
     *
     * @return PlaylistEntryObject
     */
    public function setArtist(?string $artist): PlaylistEntryObject
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getArtistId(): ?int
    {
        return $this->artistId;
    }

    /**
     * @param int|null $artistId
     *
     * @return PlaylistEntryObject
     */
    public function setArtistId(?int $artistId): PlaylistEntryObject
    {
        $this->artistId = $artistId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     *
     * @return PlaylistEntryObject
     */
    public function setTitle(?string $title): PlaylistEntryObject
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSongId(): ?int
    {
        return $this->songId;
    }

    /**
     * @param int|null $songId
     *
     * @return PlaylistEntryObject
     */
    public function setSongId(?int $songId): PlaylistEntryObject
    {
        $this->songId = $songId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAlbum(): ?string
    {
        return $this->album;
    }

    /**
     * @param null|string $album
     *
     * @return PlaylistEntryObject
     */
    public function setAlbum(?string $album): PlaylistEntryObject
    {
        $this->album = $album;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAlbumId(): ?int
    {
        return $this->albumId;
    }

    /**
     * @param int|null $albumId
     *
     * @return PlaylistEntryObject
     */
    public function setAlbumId(?int $albumId): PlaylistEntryObject
    {
        $this->albumId = $albumId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    /**
     * @param null|string $thumbnail
     *
     * @return PlaylistEntryObject
     */
    public function setThumbnail(?string $thumbnail): PlaylistEntryObject
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getAdded(): ?\DateTime
    {
        return $this->added;
    }

    /**
     * @param \DateTime|null $added
     *
     * @return PlaylistEntryObject
     */
    public function setAdded(?\DateTime $added): PlaylistEntryObject
    {
        $this->added = $added;

        return $this;
    }
}