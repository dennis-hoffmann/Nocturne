<?php

namespace App\Document\Object;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * @ES\ObjectType
 */
class ArtistObject
{
    /**
     * @ES\Property(type="integer")
     *
     * @var int|null
     */
    private $id;

    /**
     * @ES\Property(type="text")
     *
     * @var string|null
     */
    private $name;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string|null
     */
    private $born;

    /**
     * @ES\Property(type="date")
     *
     * @var \DateTime|null
     */
    private $dateadded;

    /**
     * @ES\Property(type="text")
     *
     * @var string|null
     */
    private $description;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string|null
     */
    private $died;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string|null
     */
    private $disbanded;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string|null
     */
    private $fanart;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string|null
     */
    private $formed;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string[]|null
     */
    private $genre;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string[]|null
     */
    private $instrument;

    /**
     * @ES\Property(type="boolean")
     *
     * @var bool|null
     */
    private $isalbumartist;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string|null
     */
    private $label;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string[]|null
     */
    private $mood;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string[]|null
     */
    private $musicbrainzartistid;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string[]|null
     */
    private $songgenres;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string[]|null
     */
    private $style;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string|null
     */
    private $thumbnail;

    /**
     * @ES\Property(type="keyword")
     *
     * @var string[]|null
     */
    private $yearsactive;

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
     * @return ArtistObject
     */
    public function setId(?int $id): ArtistObject
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     *
     * @return ArtistObject
     */
    public function setName(?string $name): ArtistObject
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getBorn(): ?string
    {
        return $this->born;
    }

    /**
     * @param null|string $born
     *
     * @return ArtistObject
     */
    public function setBorn(?string $born): ArtistObject
    {
        $this->born = $born;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateadded(): ?\DateTime
    {
        return $this->dateadded;
    }

    /**
     * @param \DateTime|null $dateadded
     *
     * @return ArtistObject
     */
    public function setDateadded(?\DateTime $dateadded): ArtistObject
    {
        $this->dateadded = $dateadded;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return ArtistObject
     */
    public function setDescription(?string $description): ArtistObject
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDied(): ?string
    {
        return $this->died;
    }

    /**
     * @param null|string $died
     *
     * @return ArtistObject
     */
    public function setDied(?string $died): ArtistObject
    {
        $this->died = $died;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDisbanded(): ?string
    {
        return $this->disbanded;
    }

    /**
     * @param null|string $disbanded
     *
     * @return ArtistObject
     */
    public function setDisbanded(?string $disbanded): ArtistObject
    {
        $this->disbanded = $disbanded;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFanart(): ?string
    {
        return $this->fanart;
    }

    /**
     * @param null|string $fanart
     *
     * @return ArtistObject
     */
    public function setFanart(?string $fanart): ArtistObject
    {
        $this->fanart = $fanart;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFormed(): ?string
    {
        return $this->formed;
    }

    /**
     * @param null|string $formed
     *
     * @return ArtistObject
     */
    public function setFormed(?string $formed): ArtistObject
    {
        $this->formed = $formed;

        return $this;
    }

    /**
     * @return null|string[]
     */
    public function getGenre(): ?array
    {
        return $this->genre;
    }

    /**
     * @param null|string[] $genre
     *
     * @return ArtistObject
     */
    public function setGenre(?array $genre): ArtistObject
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * @return null|string[]
     */
    public function getInstrument(): ?array
    {
        return $this->instrument;
    }

    /**
     * @param null|string[] $instrument
     *
     * @return ArtistObject
     */
    public function setInstrument(?array $instrument): ArtistObject
    {
        $this->instrument = $instrument;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsalbumartist(): ?bool
    {
        return $this->isalbumartist;
    }

    /**
     * @param bool|null $isalbumartist
     *
     * @return ArtistObject
     */
    public function setIsalbumartist(?bool $isalbumartist): ArtistObject
    {
        $this->isalbumartist = $isalbumartist;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param null|string $label
     *
     * @return ArtistObject
     */
    public function setLabel(?string $label): ArtistObject
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return null|string[]
     */
    public function getMood(): ?array
    {
        return $this->mood;
    }

    /**
     * @param null|string[] $mood
     *
     * @return ArtistObject
     */
    public function setMood(?array $mood): ArtistObject
    {
        $this->mood = $mood;

        return $this;
    }

    /**
     * @return null|string[]
     */
    public function getMusicbrainzartistid(): ?array
    {
        return $this->musicbrainzartistid;
    }

    /**
     * @param null|string[] $musicbrainzartistid
     *
     * @return ArtistObject
     */
    public function setMusicbrainzartistid(?array $musicbrainzartistid): ArtistObject
    {
        $this->musicbrainzartistid = $musicbrainzartistid;

        return $this;
    }

    /**
     * @return null|string[]
     */
    public function getSonggenres(): ?array
    {
        return $this->songgenres;
    }

    /**
     * @param null|string[] $songgenres
     *
     * @return ArtistObject
     */
    public function setSonggenres(?array $songgenres): ArtistObject
    {
        $this->songgenres = $songgenres;

        return $this;
    }

    /**
     * @return null|string[]
     */
    public function getStyle(): ?array
    {
        return $this->style;
    }

    /**
     * @param null|string[] $style
     *
     * @return ArtistObject
     */
    public function setStyle(?array $style): ArtistObject
    {
        $this->style = $style;

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
     * @return ArtistObject
     */
    public function setThumbnail(?string $thumbnail): ArtistObject
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * @return null|string[]
     */
    public function getYearsactive(): ?array
    {
        return $this->yearsactive;
    }

    /**
     * @param null|string[] $yearsactive
     *
     * @return ArtistObject
     */
    public function setYearsactive(?array $yearsactive): ArtistObject
    {
        $this->yearsactive = $yearsactive;

        return $this;
    }
}