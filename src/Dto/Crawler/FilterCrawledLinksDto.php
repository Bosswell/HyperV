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
     * @Assert\NotNull(
     *     message = "You need to specify filtering pattern"
     * )
     * @var string
     */
    private $pattern;

    public function __construct(?array $data = null)
    {
        $this->crawlingHistoryId = (int)$data['crawlingHistoryId'] ?? null;
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