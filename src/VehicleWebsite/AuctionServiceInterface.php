<?php


namespace App\VehicleWebsite;

interface AuctionServiceInterface
{
    /** @return AuctionOverviewInterface[] */
    public function getAuctionsOverviews(): array;

    public function getServiceName(): string;
}