<?php

namespace App\Dto\Crawler;

use Symfony\Component\Validator\Constraints as Assert;

final class CrawlerGetLinks
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
     * @var string
     */
    private $pattern;

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
            $this->pattern = $data['pattern'] ?? '';
            $this->excludedPaths = $data['excludedPaths'] ?? [];
            $this->limit = $data['limit'] ?? 0;
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
     * @return string
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
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
    public function getEncodedPattern(): string
    {
        return base64_encode('pattern_' . $this->domainUrl . $this->pattern ?? '');
    }

    /**
     * @return string
     */
    public function getEncodedDomain(): string
    {
        return base64_encode($this->domainUrl);
    }
}
