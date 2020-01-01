<?php

namespace App\Command;

use App\Message\Crawler\CrawlDomainLinksMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;


class WebCrawlerCommand extends Command
{
    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        parent::__construct('crawler:extract:domain-links');

        $this->messageBus = $messageBus;
    }

    protected function configure()
    {
        $this
            ->addOption('domainUrl', 'd', InputOption::VALUE_REQUIRED, 'Website base domain url ex. http://youtube.com/')
            ->addOption('excludedPaths', 'p',InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Excluded paths ex. /pl/, /de/, ?search')
            ->addOption('limit', 'l',InputOption::VALUE_OPTIONAL, 'How many urls you want to crawl')
            ->addOption('crawlingHistoryId', 'c',InputOption::VALUE_OPTIONAL, 'Historical crawling id which you want to continue')
        ;
    }

    /**
     * @return int
     * @param OutputInterface $output
     * @param InputInterface $input
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = new CrawlDomainLinksMessage($input->getOptions());

        $output->writeln('Extracting domain urls..');
        $output->writeln('Visit /var/log/linkCrawler.log to see more details');

        $this->messageBus->dispatch($message);

        $output->writeln('Links has been extracted');

        return 0;
    }
}