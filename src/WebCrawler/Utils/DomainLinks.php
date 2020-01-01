<?php

namespace App\WebCrawler\Utils;

class DomainLinks
{
    /** @var int */
    private $extractedLinks;

    /** @var int */
    private $crawledLinks;

    /** @var string */
    private $fileName;

    public function __construct(int $extractedLinks, int $crawledLinks, string $fileName)
    {
        $this->extractedLinks = $extractedLinks;
        $this->crawledLinks = $crawledLinks;
        $this->fileName = $fileName;
    }

    /**
     * @return int|null
     */
    public function getExtractedLinks(): int
    {
        return $this->extractedLinks;
    }

    /**
     * @return int|null
     */
    public function getCrawledLinks(): int
    {
        return $this->crawledLinks;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }
}