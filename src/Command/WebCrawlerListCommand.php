<?php

namespace App\Command;

use App\Entity\CrawlingHistory;
use App\WebCrawler\WebCrawlerFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebCrawlerListCommand extends Command
{
    /** @var WebCrawlerFacade */
    private $webCrawlerFacade;

    public function __construct(WebCrawlerFacade $webCrawlerFacade)
    {
        parent::__construct('crawler:list:domain-crawling-history');

        $this->webCrawlerFacade = $webCrawlerFacade;
    }

    protected function configure()
    {
        $this->setDescription('Get list of historical domain crawling');
        $this->addArgument('domain', InputArgument::REQUIRED, 'Domain name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain = $input->getArgument('domain');

        $crawlingHistories = $this->webCrawlerFacade->getDomainCrawlingHistory($domain);

        if (is_null($crawlingHistories)) {
            return 0;
        }

        $table = new Table($output);
        $table
            ->setHeaders(['id', 'crawledLinks', 'extractedLinks', 'createdAt'])
            ->setRows(array_map(function (CrawlingHistory $crawlingHistory) {
                return [
                    $crawlingHistory->getId(),
                    $crawlingHistory->getCrawledLinks(),
                    $crawlingHistory->getExtractedLinks(),
                    $crawlingHistory->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }, $crawlingHistories->toArray()))
        ;

        $table->render();

        return 0;
    }
}