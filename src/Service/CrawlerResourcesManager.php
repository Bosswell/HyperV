<?php

namespace App\Service;

use App\Dto\Crawler\FilterCrawledLinksDto;
use App\Entity\CrawlingHistory;
use App\WebCrawler\Utils\DomainLinks;
use App\WebCrawler\Utils\UrlPath;
use SplFileObject;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class CrawlerResourcesManager
{
    /** @var string */
    private $crawledLinksDir;

    /** @var ParameterBagInterface */
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
        $this->crawledLinksDir = $parameterBag->get('crawled.links.dir');
    }

    public function getDomainCrawledLinksFile(UrlPath $urlPath, ?DomainLinks $domainLinks = null): SplFileObject
    {
        if (!is_null($domainLinks)) {
            $fileName = $domainLinks->getFileName();
        } else {
            $date = new \DateTime('now');

            $fileName = sprintf(
                '%s__%s.txt',
                $urlPath->getDomain(),
                $date->format('Y-m-d__H_i_s')
            );
        }

        $pageLinksFile = $this->parameterBag->get('crawled.links.dir') . $fileName;

        $file = new SplFileObject($pageLinksFile, 'a+');
        $file->fwrite($urlPath->getUrl(). "\n");

        return $file;
    }

    /**
     * @throws Exception
     * @param string $domainName
     * @param FilterCrawledLinksDto $filterCrawledLinksDto
     */
    public function removeFilteredDomainLinks(FilterCrawledLinksDto $filterCrawledLinksDto, string $domainName): void
    {
        $filePath = sprintf(
            '%s%s%s.txt',
            $this->crawledLinksDir,
            $domainName,
            $filterCrawledLinksDto->getEncodedPattern()
        );

        if (
            file_exists($filePath)
            && !unlink($filePath)
        ) {
            throw new Exception('Unable to remove file with filtered links');
        }
    }

    /**
     * @return array [SplFileObject $file, int $filteredLinks]
     * @throws Exception
     * @param FilterCrawledLinksDto $filterDomainLinksDto
     * @param CrawlingHistory $crawlingHistory
     */
    public function filterDomainLinksByPattern(FilterCrawledLinksDto $filterDomainLinksDto, CrawlingHistory $crawlingHistory): array
    {
        $rawDomainLinks = $this->getRawDomainLinks($crawlingHistory);
        $crawledLinksPatternsDir = $this->crawledLinksDir . $crawlingHistory->getDomain()->getName();

        if (
            !is_dir($this->crawledLinksDir . $crawlingHistory->getFileName())
            && !mkdir($crawledLinksPatternsDir)
        ) {
            throw new Exception('Unable to create directory for crawling patterns.');
        }

        $domainLinksPatternFile = new SplFileObject(sprintf(
            '%s%s.txt',
            $crawledLinksPatternsDir,
            $filterDomainLinksDto->getEncodedPattern()
        ), 'a+');

        $filteredLinks = 0;
        while ($rawDomainLinks->eof()) {
            $line = $rawDomainLinks->fgets();
            if (preg_match(sprintf('/%s/', $filterDomainLinksDto->getPattern()), $line)) {
                $domainLinksPatternFile->fwrite(sprintf("%s\n", $line));
                $filteredLinks++;
            }
        }

        return [
            $domainLinksPatternFile,
            $filteredLinks
        ];
    }

    /**
     * @return SplFileObject
     * @throws Exception
     * @param CrawlingHistory $crawlingHistory
     */
    private function getRawDomainLinks(CrawlingHistory $crawlingHistory): SplFileObject
    {
        $rawLinksFile = new SplFileObject($this->crawledLinksDir . $crawlingHistory->getFileName());

        if (!$rawLinksFile->isFile()) {
            throw new Exception('File with raw domain links does not exists. You need to crawl entire domain first.');
        }

        return $rawLinksFile;
    }
}