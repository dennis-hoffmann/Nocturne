<?php

namespace App\Document;

use App\Document\Object\ArtistObject;
use ONGR\ElasticsearchBundle\Annotation as ES;
use ONGR\ElasticsearchBundle\Result\ObjectIterator;

/**
 * @ES\Index(alias="songs", default=true)
 */
class Song
{
    public function __construct()
    {
        $this->genre = [];
    }

    /**
     * @var int|null
     *
     * @ES\Id()
     */
    private $id;

    /**
     * @var string|null
     *
     * @ES\Property(type="text", fields={"keyword"={"type"="keyword"}})
     */
    private $title;

    /**
     * @var ArtistObject|null
     *
     * @ES\Embedded(class="App\Document\Object\ArtistObject")
     */
    private $artist;

    /**
     * @ES\Property(type="text", fields={"keyword"={"type"="keyword"}})
     *
     * @var string|null
     */
    private $artistname;

    /**
     * @var int|null
     *
     * @ES\Property(type="integer")
     */
    private $artistid;

    /**
     * @var string|null
     *
     * @ES\Property(type="text", fields={"keyword"={"type"="keyword"}})
     */
    private $album;

    /**
     * @var int|null
     *
     * @ES\Property(type="integer")
     */
    private $albumId;

    /**
     * @var \DateTime|null
     *
     * @ES\Property(type="date")
     */
    private $added;

    /**
     * @var string|null
     *
     * @ES\Property(type="keyword")
     */
    private $file;

    /**
     * @var string|null
     *
     * @ES\Property(type="keyword")
     */
    private $playableFile;

    /**
     * @var string|null
     *
     * @ES\Property(type="text")
     */
    private $lyrics;

    /**
     * @var string|null
     *
     * @ES\Property(type="text")
     */
    private $rawLyrics;

    /**
     * @var string|null
     *
     * @ES\Property(type="keyword")
     */
    private $mood;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string[]
     */
    private $genre;

    /**
     * @var int|null
     *
     * @ES\Property(type="integer")
     */
    private $playCount;

    /**
     * @var int|null
     *
     * @ES\Property(type="integer")
     */
    private $track;

    /**
     * @var string|null
     *
     * @ES\Property(type="keyword")
     */
    private $thumbnail;

    /**
     * @var int|null
     *
     * @ES\Property(type="integer")
     */
    private $userRating;

    /**
     * @var int|null
     *
     * @ES\Property(type="integer")
     */
    private $votes;

    /**
     * @var int|null
     *
     * @ES\Property(type="integer")
     */
    private $year;

    /**
     * @var \DateTime|null
     *
     * @ES\Property(type="date")
     */
    private $lastPlayed;
    /**
     * @var int
     *
     * @ES\Property(type="integer")
     */
    private $length;

    /**
     * @var ?string
     *
     * @ES\Property(type="text", settings={"index":"false"})
     */
    private $waveform;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Song
     */
    public function setId(int $id): Song
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     *
     * @return Song
     */
    public function setTitle(?string $title): Song
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getArtistname(): ?string
    {
        return $this->artistname;
    }

    /**
     * @param null|string $artistname
     *
     * @return Song
     */
    public function setArtistname(?string $artistname): Song
    {
        $this->artistname = $artistname;

        return $this;
    }

    /**
     * @return null|ArtistObject
     */
    public function getArtist()
    {
        if ($this->artist instanceof ObjectIterator) {
            return $this->artist->first();
        }

        return $this->artist;
    }

    /**
     * @param null|ArtistObject $artist
     *
     * @return Song
     */
    public function setArtist($artist): Song
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getArtistid(): ?int
    {
        return $this->artistid;
    }

    /**
     * @param int|null $artistid
     *
     * @return Song
     */
    public function setArtistid(?int $artistid): Song
    {
        $this->artistid = $artistid;

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
     * @return Song
     */
    public function setAlbum(?string $album): Song
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
     * @return Song
     */
    public function setAlbumId(?int $albumId): Song
    {
        $this->albumId = $albumId;

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
     * @return Song
     */
    public function setAdded(?\DateTime $added): Song
    {
        $this->added = $added;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * @param null|string $file
     *
     * @return Song
     */
    public function setFile(?string $file): Song
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPlayableFile(): ?string
    {
        return $this->playableFile;
    }

    /**
     * @param null|string $file
     *
     * @return Song
     */
    public function setPlayableFile(?string $file): Song
    {
        $this->playableFile = $file;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLyrics(): ?string
    {
        return $this->lyrics;
    }

    /**
     * @param null|string $lyrics
     *
     * @return Song
     */
    public function setLyrics(?string $lyrics): Song
    {
        $this->lyrics = $lyrics;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRawLyrics(): ?string
    {
        return $this->rawLyrics;
    }

    /**
     * @param null|string $rawLyrics
     *
     * @return Song
     */
    public function setRawLyrics(?string $rawLyrics): Song
    {
        $this->rawLyrics = $rawLyrics;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMood(): ?string
    {
        return $this->mood;
    }

    /**
     * @param null|string $mood
     *
     * @return Song
     */
    public function setMood(?string $mood): Song
    {
        $this->mood = $mood;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * @param string[] $genre
     *
     * @return Song
     */
    public function setGenre($genre): Song
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * @param string $genre
     *
     * @return Song
     */
    public function addGenre(string $genre): Song
    {
        $this->genre[] = $genre;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPlayCount(): ?int
    {
        return $this->playCount;
    }

    /**
     * @param int|null $playCount
     *
     * @return Song
     */
    public function setPlayCount(?int $playCount): Song
    {
        $this->playCount = $playCount;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getTrack(): ?int
    {
        return $this->track;
    }

    /**
     * @param null|int $track
     *
     * @return Song
     */
    public function setTrack(?int $track): Song
    {
        $this->track = $track;

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
     * @return Song
     */
    public function setThumbnail(?string $thumbnail): Song
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getUserRating(): ?int
    {
        return $this->userRating;
    }

    /**
     * @param int|null $userRating
     *
     * @return Song
     */
    public function setUserRating(?int $userRating): Song
    {
        $this->userRating = $userRating;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getVotes(): ?int
    {
        return $this->votes;
    }

    /**
     * @param int|null $votes
     *
     * @return Song
     */
    public function setVotes(?int $votes): Song
    {
        $this->votes = $votes;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getYear(): ?int
    {
        return $this->year;
    }

    /**
     * @param int|null $year
     *
     * @return Song
     */
    public function setYear(?int $year): Song
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastPlayed(): ?\DateTime
    {
        return $this->lastPlayed;
    }

    /**
     * @param \DateTime|null $lastPlayed
     *
     * @return Song
     */
    public function setLastPlayed(?\DateTime $lastPlayed): Song
    {
        $this->lastPlayed = $lastPlayed;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * @param int|null $length
     *
     * @return Song
     */
    public function setLength(?int $length): Song
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getWaveform(): ?string
    {
        return $this->waveform;
    }

    /**
     * @param string|null $waveform
     *
     * @return Song
     */
    public function setWaveform(?string $waveform): Song
    {
        $this->waveform = $waveform;

        return $this;
    }
}