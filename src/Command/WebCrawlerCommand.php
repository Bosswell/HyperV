<?php

namespace App\Command;

use App\Dto\Crawler\CrawlerGetDomainLinks;
use App\Exception\ValidationException;
use App\PageExtractor\LinkExtractorFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class WebCrawlerCommand extends Command
{
    /** @var LinkExtractorFacade */
    private $linkExtractorFacade;

    public function __construct(LinkExtractorFacade $linkExtractorFacade)
    {
        parent::__construct('crawler:extract:domain:links');

        $this->linkExtractorFacade = $linkExtractorFacade;
    }

    protected function configure()
    {
        $this
            ->addOption('domainUrl', 'd', InputOption::VALUE_REQUIRED, 'Website base domain url ex. http://youtube.com/')
            ->addOption('excludedPaths', 'p',InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Excluded paths ex. /pl/, /de/, ?search')
            ->addOption('limit', 'l',InputOption::VALUE_OPTIONAL, 'How many urls')
            ->addOption('continueCrawling', 'c',InputOption::VALUE_OPTIONAL, 'Continue crawling a site', false)
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
        $this->linkExtractorFacade->getDomainLinks($crawlerGetDomainLinks, $input->getOption('limit'),  (bool)$input->getOption('continueCrawling'));

        return 0;
    }
}