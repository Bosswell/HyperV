<?php


namespace App\VehicleWebsite;

use App\Message\Crawler\CrawlAuctionsOverviewsMessage;

interface AuctionServiceInterface
{
    /**
     * @param CrawlAuctionsOverviewsMessage $crawlAuctionsOverviewsMessage
     * @return AuctionOverview[]
     */
    public function getAuctionsOverviews(CrawlAuctionsOverviewsMessage $crawlAuctionsOverviewsMessage): array;
}