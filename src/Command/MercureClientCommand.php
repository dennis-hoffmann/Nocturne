<?php

namespace App\Command;

use App\Mercure\SubscriberFactory;
use Clue\React\EventSource\MessageEvent;
use React\EventLoop\Factory as LoopFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MercureClientCommand extends Command
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    public function __construct(SubscriberFactory $subscriberFactory)
    {
        $this->subscriberFactory = $subscriberFactory;

        parent::__construct('app:mercure:subscribe');
    }

    protected function configure()
    {
        $this->addArgument(
            'topics',
            InputArgument::IS_ARRAY,
            'Topics to subscribe to'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $topics = $input->getArgument('topics');

        $loop = LoopFactory::create();
        $es = $this->subscriberFactory->create($topics, $loop);

        $es->on('message', function (MessageEvent $message) use ($output) {
            $output->writeln(print_r($message));
        });

        $loop->run();

        return 0;

    }
}