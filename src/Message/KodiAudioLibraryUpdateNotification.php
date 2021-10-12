<?php

namespace App\Message;

class KodiAudioLibraryUpdateNotification
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var bool
     */
    private $force;

    public function __construct(string $id, bool $force = false)
    {
        $this->id = $id;
        $this->force = $force;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function isForce(): bool
    {
        return $this->force;
    }
}