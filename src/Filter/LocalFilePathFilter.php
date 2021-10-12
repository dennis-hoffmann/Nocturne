<?php

namespace App\Filter;

use App\Http\Kodi;

class LocalFilePathFilter
{
    /**
     * @var bool
     */
    private $enable;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string
     */
    private $replacement;

    /**
     * @var Kodi
     */
    private $kodi;

    public function __construct(bool $enable, string $pattern, string $replacement, Kodi $kodi)
    {
        $this->pattern = $pattern;
        $this->replacement = $replacement;
        $this->enable = $enable;
        $this->kodi = $kodi;
    }

    public function filter(string $value): string
    {
        return $this->enable
            ? preg_replace($this->pattern, $this->replacement, $value)
            : $this->kodi->kodiFile($value)
        ;
    }

    public function needsReplacing(string $currentLink)
    {
        return preg_match($this->pattern, $currentLink);
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }
}
