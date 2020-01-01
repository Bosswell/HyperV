<?php

namespace App\Command;

use App\Message\Crawler\FilterCrawledLinksMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class WebCrawlerFilterLinksCommand extends Command
{
    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        parent::__construct('crawler:filter:domain-links');

        $this->messageBus = $messageBus;
    }

    protected function configure()
    {
        $this
            ->addOption('crawlingHistoryId', 'i', InputOption::VALUE_REQUIRED)
            ->addOption('pattern', 'p',InputOption::VALUE_REQUIRED, 'Urls filter pattern')
            ->addOption('refresh', 'r', InputOption::VALUE_OPTIONAL, 'true if you want to refresh filtered links, false otherwise', false)
        ;
    }

    /**
     * @return int
     * @param OutputInterface $output
     * @param InputInterface $input
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = new FilterCrawledLinksMessage($input->getOptions());

        $output->writeln('Filtering links..');
        $this->messageBus->dispatch($message);
        $output->writeln('Links has been filtered');

        return 0;
    }
}