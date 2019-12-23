<?php

namespace App\Command;

use App\Dto\Crawler\CrawlerGetDomainLinks;
use App\Exception\ValidationException;
use App\WebCrawler\WebCrawlerFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class WebCrawlerCommand extends Command
{
    /** @var WebCrawlerFacade */
    private $webCrawlerFacade;

    public function __construct(WebCrawlerFacade $webCrawlerFacade)
    {
        parent::__construct('crawler:extract:domain:links');

        $this->webCrawlerFacade = $webCrawlerFacade;
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
     * @throws ValidationException
     * @throws Throwable
     * @param OutputInterface $output
     * @param InputInterface $input
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $crawlerGetDomainLinks = new CrawlerGetDomainLinks($input->getOptions());

        $output->writeln('Extracting domain urls..');
        $output->writeln('Visit /var/log/linkCrawler.log to see more details');

        $this->webCrawlerFacade->crawlDomainLinks(
            $crawlerGetDomainLinks,
            $input->getOption('limit'),
            $input->getOption('crawlingHistoryId')
        );

        return 0;
    }
}