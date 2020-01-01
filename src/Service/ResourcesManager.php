<?php

namespace App\Service;

use App\Dto\Crawler\FilterCrawledLinksDto;
use App\Entity\CrawlingHistory;
use App\WebCrawler\Utils\DomainLinks;
use App\WebCrawler\Utils\UrlPath;
use SplFileObject;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class ResourcesManager
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
     * @param string $encodedPattern
     */
    public function removeFilteredDomainLinks(string $encodedPattern, string $domainName): void
    {
        $filePath = sprintf(
            '%s%s%s.txt',
            $this->crawledLinksDir,
            $domainName,
            $encodedPattern
        );

        if (
            file_exists($filePath)
            && !unlink($filePath)
        ) {
            throw new Exception('Unable to remove file with filtered links');
        }
    }

    /**
     * @return SplFileObject
     * @throws Exception
     * @param string $domainName
     * @param string $fileName
     */
    public function getFilteredDomainLinksFile(string $domainName, string $fileName): SplFileObject
    {
        $crawledLinksPatternsDir = $this->crawledLinksDir . $domainName;

        if (
            !is_dir($crawledLinksPatternsDir)
            && !mkdir($crawledLinksPatternsDir)
        ) {
            throw new Exception('Unable to create directory for crawling patterns.');
        }

        return new SplFileObject(sprintf(
            '%s/%s.txt',
            $crawledLinksPatternsDir,
            $fileName
        ), 'a+');
    }

    /**
     * @return int -> quantity of filtered links
     * @throws Exception
     * @param CrawlingHistory $crawlingHistory
     * @param string $pattern
     * @param SplFileObject $domainLinksPatternFile
     */
    public function filterDomainLinksByPattern(
        string $pattern,
        SplFileObject $domainLinksPatternFile,
        CrawlingHistory $crawlingHistory
    ): int {
        $rawDomainLinks = $this->getRawDomainLinks($crawlingHistory);
        $filteredLinks = 0;

        while ($line = rtrim($rawDomainLinks->fgets())) {
            if (preg_match(sprintf('/%s/', $pattern), $line)) {
                $domainLinksPatternFile->fwrite(sprintf("%s\n", $line));
                $filteredLinks++;
            }
        }

        return $filteredLinks;
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