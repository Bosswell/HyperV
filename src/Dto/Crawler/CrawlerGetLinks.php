<?php

namespace App\Dto\Crawler;

use Symfony\Component\Validator\Constraints as Assert;

final class CrawlerGetLinks
{
    /**
     * @var string
     *
     * @Assert\Type("string")
     * @Assert\NotNull(
     *     message = "You need to specify crawler name"
     * )
     */
    private $name;

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
     */
    private $pattern;

    /**
     * @var string
     *
     * @Assert\Type("string")
     * @Assert\NotNull(
     *     message = "You need to specify your pattern"
     * )
     */
    private $patternName;

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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPatternName(): string
    {
        return $this->patternName;
    }
}
