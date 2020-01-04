<?php

namespace App\Message\Crawler;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CrawlDomainLinksMessage
{
    /**
     * @var string
     *
     * @Assert\NotNull(
     *     message = "You need to specify domain url"
     * )
     * @Assert\Url(
     *    message = "The domainUrl '{{ value }}' is not a valid url",
     *    protocols = {"http", "https"}
     * )
     */
    private $domainUrl;

    /**
     * @var int -> value 0 mean that there is no limit
     *
     * @Assert\Type(
     *     type="integer",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\GreaterThanOrEqual(0)
     */
    private $limit = 0;

    /**
     * @var UuidInterface
     *
     * @Assert\Type(
     *     type="Ramsey\Uuid\UuidInterface",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     */
    private $crawlingHistoryId;


    /**
     * @var string[]
     */
    private $excludedPaths = [];

    public function __construct(?array $data = null)
    {
        if (!is_null($data)) {
            $this->domainUrl = $data['domainUrl'] ?? null;
            $this->excludedPaths = $data['excludedPaths'] ?? [];
            $this->limit = (int)$data['limit'] ?? 0;
            $this->crawlingHistoryId = $data['crawlingHistoryId'] ?? null;
        }
    }

    /**
     * @return UuidInterface|null
     */
    public function getCrawlingHistoryId(): ?UuidInterface
    {
        return $this->crawlingHistoryId;
    }

    /**
     * @return string
     */
    public function getDomainUrl(): string
    {
        return $this->domainUrl;
    }

    /**
     * @return string[]
     */
    public function getExcludedPaths(): array
    {
        return $this->excludedPaths;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return string
     */
    public function getEncodedDomain(): string
    {
        return base64_encode($this->domainUrl);
    }
}