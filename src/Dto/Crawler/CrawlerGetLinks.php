<?php

namespace App\Dto\Crawler;

use Symfony\Component\Validator\Constraints as Assert;

class CrawlerGetLinks
{
    /**
     * @var string
     *
     * @Assert\NotNull(
     *     message = "You need to specify your domain"
     * )
     */
    private $domainUrl;

    /**
     * @var string
     *
     * @Assert\NotNull(
     *     message = "You need to specify your pattern"
     * )
     */
    private $pattern;

    /**
     * @var string[]
     */
    private $excludedPaths;

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
    public function getPattern(): string
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
}