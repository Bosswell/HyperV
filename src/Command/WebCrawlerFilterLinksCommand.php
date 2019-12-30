<?php

namespace App\Command;

use App\Dto\Crawler\FilterCrawledLinksDto;
use App\WebCrawler\WebCrawlerFacadee;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WebCrawlerFilterLinksCommand extends Command
{
    /** @var WebCrawlerFacadee */
    private $webCrawlerFacade;

    public function __construct(WebCrawlerFacadee $webCrawlerFacade)
    {
        parent::__construct('crawler:filter:links');

        $this->webCrawlerFacade = $webCrawlerFacade;
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
     * @return int|null
     * @throws EntityNotFoundException
     * @param OutputInterface $output
     * @param InputInterface $input
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $crawlerGetDomainLinks = new FilterCrawledLinksDto($input->getOptions());

        $output->writeln('Filtering links..');
        $this->webCrawlerFacade->getDomainLinksByPattern($crawlerGetDomainLinks, $input->getOption('refresh'));
        $output->writeln('Links has been filtered');

        return 0;
    }
}