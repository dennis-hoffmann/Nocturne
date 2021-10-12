<?php

namespace App\MessageHandler;

use App\Message\KodiAudioLibraryUpdateNotification;
use App\Message\WebSocketNotification;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class WebSocketNotificationHandler implements MessageHandlerInterface
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function __invoke(WebSocketNotification $message)
    {
        if ($message->getMethod() === 'AudioLibrary.OnUpdate' && $message->getParams()['data']['type'] ?? null === 'song') {
            $this->bus->dispatch(new KodiAudioLibraryUpdateNotification($message->getParams()['data']['id']));
        }

        if ($message->getMethod() === 'AudioLibrary.OnScanStarted') {
            $this->bus->dispatch(new Update('audio_update', json_encode([
                'event' => 'audio_update',
                'data'  => [
                    'message' => 'Audio library scan started.'
                ],
            ])));
        }

        if ($message->getMethod() === 'AudioLibrary.OnScanFinished') {
            $this->bus->dispatch(new Update('audio_update', json_encode([
                'event' => 'audio_update',
                'data'  => [
                    'message' => 'Audio library scan finished.'
                ],
            ])));
        }

        $this->bus->dispatch(new Update('general', json_encode([
            'event' => $message->getMethod(),
            'data'  => [
                'method' => $message->getMethod(),
                'params' => $message->getParams(),
            ],
        ])));
    }
}