<?php

namespace App\Twig;

use App\Service\Mercure;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MercureExtension extends AbstractExtension
{
    /**
     * @var string
     */
    private $hubUrl;

    /**
     * @var Mercure
     */
    private $mercure;

    public function __construct(string $hubUrl, Mercure $mercure)
    {
        $this->hubUrl = $hubUrl;
        $this->mercure = $mercure;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('mercure_hub_url', [$this, 'hubUrl']),
            new TwigFunction('mercure_topics', [$this, 'topics']),
        ];
    }

    public function hubUrl(): string
    {
        return $this->hubUrl;
    }

    public function topics(): string
    {
        return json_encode($this->mercure->getTopics());
    }
}