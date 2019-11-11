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
     * @param string $domainUrl
     */
    public function setDomainUrl(string $domainUrl): void
    {
        $this->domainUrl = $domainUrl;
    }
}