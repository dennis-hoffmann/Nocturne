<?php

namespace App\Command;

use App\Message\WebSocketNotification;
use Psr\Log\LoggerInterface;
use Ratchet\Client;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class WebSocketClientCommand extends Command
{
    /**
     * @var string
     */
    private $webSocketUrl;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        string $name,
        string $webSocketUrl,
        MessageBusInterface $bus,
        LoggerInterface $logger
    ) {
        parent::__construct($name);

        $this->webSocketUrl = $webSocketUrl;
        $this->bus = $bus;
        $this->logger = $logger;
    }


    protected function configure()
    {
        $this->setDescription('Run WebSocket client.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Client\connect('ws://' . $this->webSocketUrl)->then(function (WebSocket $ws) {
            $bus = $this->bus;
            $this->logger->info(sprintf('Successfully connected to %s', $this->webSocketUrl));

            $ws->on('message', function (Message $msg) use ($bus) {
                $json = json_decode($msg->getPayload(), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->logger->warning(sprintf('Received invalid JSON: %s', json_last_error_msg()));
                } else {
                    $this->logger->debug(sprintf('Dispatching Kodi Event: "%s" with data: %s', $json['method'], json_encode($json['params'] ?? [])));

                    $bus->dispatch(new WebSocketNotification($json['method'], $json['params'] ?? []));
                }
            });
        }, function (\Exception $e) {
            $this->logger->error(sprintf('Could not connect: %s', $e->getMessage()));
        });

        return 0;
    }
}