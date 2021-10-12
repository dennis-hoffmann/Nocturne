<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageCommand extends Command
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        parent::__construct();

        $this->bus = $bus;
    }

    protected function configure()
    {
        $this->setName('message')
            ->setDescription('Test messages');

        $this->addArgument('topic', InputArgument::REQUIRED);
        $this->addArgument('event', InputArgument::REQUIRED);
        $this->addArgument('content', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bus->dispatch(new Update(
            $input->getArgument('topic'),
            json_encode([
                'event' => $input->getArgument('event'),
                'data'  => $input->getArgument('content'),
            ])
        ));

        return 0;
    }
}