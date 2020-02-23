<?php


namespace App\Message\Crawler;

use App\Message\Crawler\AuctionDetails\OtomotoDetails;
use App\VehicleWebsite\AuctionOverview;
use Symfony\Component\Validator\Constraints as Assert;

class CrawlAuctionsOverviewsMessage
{
    /**
     * @Assert\Choice(choices=AuctionOverview::AVAILABLE_TYPES, message="Choose a valid auction type.")
     */
    private string $type;

    /**
     * @Assert\NotNull
     */
    private string $searchQuery;

    /**
     * @Assert\GreaterThanOrEqual(1)
     */
    private int $pageNumber;

    /**
     * @Assert\Valid
     */
    private OtomotoDetails $otomotoDetails;

    public function __construct(?array $data = null)
    {
        $this->type = $data['type'] ?? '';
        $this->searchQuery = $data['searchQuery'] ?? '';
        $this->pageNumber = $data['pageNumber'] ?? 1;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSearchQuery(): string
    {
        return $this->searchQuery;
    }

    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    /**
     * @return OtomotoDetails
     */
    public function getOtomotoDetails(): OtomotoDetails
    {
        return $this->otomotoDetails;
    }

    /**
     * @param OtomotoDetails $otomotoDetails
     */
    public function setOtomotoDetails(OtomotoDetails $otomotoDetails): void
    {
        $this->otomotoDetails = $otomotoDetails;
    }
}