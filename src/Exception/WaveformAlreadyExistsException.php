<?php

namespace App\Exception;

use App\Entity\AudioWaveform;

class WaveformAlreadyExistsException extends \Exception
{
    /**
     * @var AudioWaveform
     */
    private $waveform;

    public function __construct(AudioWaveform $waveform)
    {
        parent::__construct();

        $this->waveform = $waveform;
    }

    /**
     * @return AudioWaveform
     */
    public function getWaveform(): AudioWaveform
    {
        return $this->waveform;
    }
}