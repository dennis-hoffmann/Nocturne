<?php

namespace App\Message;

class WebSocketNotification
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $params;

    public function __construct(string $method, array $params)
    {
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}