<?php


namespace App\Dto\Crawler;

use Symfony\Component\Validator\Constraints as Assert;

class FilterCrawledLinksDto
{
    /**
     * @Assert\Type(
     *     type="int",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @var int
     */
    private $crawlingHistoryId;

    /**
     * @var string
     */
    private $pattern;

    public function __construct(?array $data = null)
    {
        $this->crawlingHistoryId = $data['crawlingHistoryId'] ?? null;
        $this->pattern = $data['pattern'] ?? null;
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

    public function getEncodedPattern(): string
    {
        return base64_encode($this->crawlingHistoryId . $this->pattern);
    }
}