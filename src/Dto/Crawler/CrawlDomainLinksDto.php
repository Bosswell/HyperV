<?php

namespace App\Dto\Crawler;

use Symfony\Component\Validator\Constraints as Assert;

final class CrawlDomainLinksDto
{
    /**
     * @var string
     *
     * @Assert\Url(
     *    message = "The domainUrl '{{ value }}' is not a valid url",
     *    protocols = {"http", "https"}
     * )
     */
    private $domainUrl;

    /**
     * @Assert\Type(
     *     type="integer",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\GreaterThanOrEqual(0)
     *
     * @var int -> value 0 mean that there is no limit
     */
    private $limit = 0;


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
        }
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
