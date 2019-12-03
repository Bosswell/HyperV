<?php

namespace App\Command;


use App\Dto\Crawler\CrawlerGetLinks;
use App\Exception\ValidationException;
use App\PageExtractor\ExtractorException;
use App\PageExtractor\LinkExtractorFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WebCrawlerCommand extends Command
{
    /** @var LinkExtractorFacade */
    private $linkExtractorFacade;

    public function __construct(LinkExtractorFacade $linkExtractorFacade)
    {
        parent::__construct('crawler:extract:links');

        $this->linkExtractorFacade = $linkExtractorFacade;
    }

    protected function configure()
    {
        $this
            ->addOption('domainUrl', 'd', InputOption::VALUE_REQUIRED, 'Website base domain url ex. http://youtube.com/')
            ->addOption('pattern', 'p',InputOption::VALUE_OPTIONAL, 'RageXp for URLs ex. /d+-.*')
            ->addOption('excludedPaths', 'exPaths',InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Excluded paths ex. /pl/, /de/, ?search')
            ->addOption('limit', 'l',InputOption::VALUE_OPTIONAL, 'How many urls')
            ->addOption('continueCrawling', '-c',InputOption::VALUE_OPTIONAL, 'Continue crawling a site')
        ;
    }

    /**
     * @return void
     * @throws ValidationException
     * @throws ExtractorException
     * @param OutputInterface $output
     * @param InputInterface $input
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $crawlerGetLinks = new CrawlerGetLinks($input->getOptions());

        $output->writeln('Extracting domain urls..');
        $output->writeln('Visit /var/log/linkCrawler.log to see more details');
        $this->linkExtractorFacade->getLinks($crawlerGetLinks, $input->getOption('limit'));
    }
}