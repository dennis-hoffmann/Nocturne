<?php

namespace App\Command;

use App\Mercure\SubscriberFactory;
use Clue\React\EventSource\MessageEvent;
use Discord\Discord;
use Discord\Parts\User\Activity;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory as LoopFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiscordGatewayCommand extends Command
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $isReady = false;
    /**
     * @var string
     */
    private $discordToken;

    public function __construct(
        string $discordToken,
        SubscriberFactory $subscriberFactory,
        LoggerInterface $logger
    ) {
        $this->discordToken = $discordToken;
        $this->subscriberFactory = $subscriberFactory;
        $this->logger = $logger;

        parent::__construct('app:discord:open');
    }

    protected function configure()
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loop = LoopFactory::create();
        $topics = ['playback_update', '/.well-known/mercure/subscriptions/{topic}{/subscriber}'];
        $es = $this->subscriberFactory->create($topics, $loop, 'general');

        $discord = new Discord([
            'token' => $this->discordToken,
            'intents' => [],
            'logger' => $this->logger,
            'loop' => $loop,
        ]);

        $discord->on('ready', function (Discord $discord) use ($output, $es) {
            $this->isReady = true;
        });

        $es->on('message', function (MessageEvent $message) use ($discord) {
            $data = json_decode($message->data, true);
            $type = $data['type'] ?? null;
            $event = $data['event'] ?? null;

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->warning(sprintf('Failed decoding incoming mercure message: "%s"', json_last_error_msg()));

                return;
            }

            // We have a new page user, or we lost one
            if ($type === 'Subscription' && $data['topic'] === 'general') {
                if ($data['active']) {
                    $this->logger->debug('New Subscriber. ' . $data['subscriber']);
                } else {
                    $this->logger->debug('Subscriber Disconnected. ' . $data['subscriber']);

                    $discord->updatePresence(null);
                }
            }

            if ($event === 'playbackUpdate') {
                $this->logger->info('Received Playback Update: ' . json_encode($data));

                if (!$this->isReady) {
                    $this->logger->info('Discord is not ready. Skipping Mercure update');

                    return;
                }

                if ($data['action'] !== 'play') {
                    return;
                }

                if (empty($data['song'])) {
                    $this->logger->warning('Expected data to have key "song"');

                    return;
                }

                $song = $data['song'];

                if (
                    empty($song['artist'])
                    || empty($song['title'])
                    || empty($song['album'])
                ) {
                    $this->logger->warning('Incomplete song data provided');

                    return;
                }

                $activity = new Activity($discord, [
                    'name' => $song['artist'],
                    'type' => Activity::TYPE_LISTENING,
                    'state' => $song['album'],
                    'details' => sprintf('%s by %s', $song['title'], $song['artist'])
                ]);

                $this->logger->info('Updating Discord Status');
                $discord->updatePresence($activity);
            }
        });

        $loop->run();

        return 1;
    }
}
