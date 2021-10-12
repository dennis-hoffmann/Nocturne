<?php

namespace App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * @ES\Index(alias="playlists", default=false)
 */
class Playlist
{
    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    /**
     * @ES\Id()
     *
     * @var int|null
     */
    private $id;

    /**
     * @ES\Property(name="name", type="text", fields={"keyword"={"type"="keyword"}})
     *
     * @var string|null
     */
    private $name;

    /**
     * @ES\Property(name="owner_id", type="integer")
     *
     * @var int|null
     */
    private $ownerId;

    /**
     * @ES\Embedded(name="entries", class="App\Document\Object\PlaylistEntryObject")
     *
     * @var ArrayCollection
     */
    private $entries;

    /**
     * @ES\Property(name="created", type="date")
     *
     * @var \DateTime|null
     */
    private $created;

    /**
     * @ES\Property(name="created", type="date")
     *
     * @var \DateTime|null
     */
    private $updated;

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
     * @return Playlist
     */
    public function setId(?int $id): Playlist
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
     * @return Playlist
     */
    public function setName(?string $name): Playlist
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getOwnerId(): ?int
    {
        return $this->ownerId;
    }

    /**
     * @param int|null $ownerId
     *
     * @return Playlist
     */
    public function setOwnerId(?int $ownerId): Playlist
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    /**
     * @return ArrayCollection|null
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param array|ArrayCollection $entries
     *
     * @return Playlist
     */
    public function setEntries($entries): Playlist
    {
        $this->entries = $entries;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime|null $created
     *
     * @return Playlist
     */
    public function setCreated(?\DateTime $created): Playlist
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdated(): ?\DateTime
    {
        return $this->updated;
    }

    /**
     * @param \DateTime|null $updated
     *
     * @return Playlist
     */
    public function setUpdated(?\DateTime $updated): Playlist
    {
        $this->updated = $updated;

        return $this;
    }
}