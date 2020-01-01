<?php

namespace App\Command;

use App\Entity\CrawlingHistory;
use App\Repository\DomainRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebCrawlerListCommand extends Command
{
    /** @var DomainRepository */
    private $domainRepository;

    public function __construct(DomainRepository $domainRepository)
    {
        parent::__construct('crawler:list:domain-crawling-history');

        $this->domainRepository = $domainRepository;
    }

    protected function configure()
    {
        $this->setDescription('Get list of historical domain crawling');
        $this->addArgument('domain', InputArgument::REQUIRED, 'Domain name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain = $input->getArgument('domain');

        $crawlingHistory = $this->domainRepository
            ->findOneBy(['name' => $domain])
            ->getCrawlingHistory();

        if (empty($crawlingHistory)) {
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
            }, $crawlingHistory->toArray()))
        ;

        $table->render();

        return 0;
    }
}