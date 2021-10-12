<?php

namespace App\Message;

class AudioWaveformGenerateNotification
{
    /**
     * @var int
     */
    private $songId;

    public function __construct(int $songId)
    {
        $this->songId = $songId;
    }

    /**
     * @return int
     */
    public function getSongId(): int
    {
        return $this->songId;
    }
}