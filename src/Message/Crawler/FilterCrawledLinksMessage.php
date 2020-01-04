<?php

namespace App\Message\Crawler;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FilterCrawledLinksMessage
{
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
     * @var string
     *
     * @Assert\NotNull(
     *     message = "You need to specify filtering pattern"
     * )
     */
    private $pattern;

    /**
     * @var bool
     *
     * @Assert\Type(
     *     type="bool",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     */
    private $refresh;

    public function __construct(?array $data = null)
    {
        $this->crawlingHistoryId = $data['crawlingHistoryId'] ?? null;
        $this->pattern = $data['pattern'] ?? null;
        $this->refresh = $data['refresh'] ?? false;
    }

    /**
     * @return bool
     */
    public function isRefresh(): bool
    {
        return $this->refresh;
    }

    /**
     * @return int
     */
    public function getCrawlingHistoryId(): int
    {
        return $this->crawlingHistoryId;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return string
     */
    public function getEncodedPattern(): string
    {
        return base64_encode($this->crawlingHistoryId . $this->pattern);
    }
}