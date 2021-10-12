<?php

namespace App\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * @ES\Index(alias="playback", default=false)
 */
class Playback
{
    /**
     * @var int
     *
     * @ES\Id()
     */
    private $id;

    /**
     * @var int
     *
     * @ES\Property(type="integer")
     */
    private $userId;

    /**
     * @var int
     *
     * @ES\Property(type="integer")
     */
    private $songId;

    /**
     * @var \DateTime
     *
     * @ES\Property(type="date")
     */
    private $created;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Playback
     */
    public function setId(int $id): Playback
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return Playback
     */
    public function setUserId(int $userId): Playback
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSongId(): ?int
    {
        return $this->songId;
    }

    /**
     * @param int $songId
     *
     * @return Playback
     */
    public function setSongId(int $songId): Playback
    {
        $this->songId = $songId;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     *
     * @return Playback
     */
    public function setCreated(\DateTime $created): Playback
    {
        $this->created = $created;

        return $this;
    }
}