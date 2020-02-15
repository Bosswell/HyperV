<?php


namespace App\VehicleWebsite;


class AuctionServiceCollector
{
    /** @var AuctionServiceInterface[] */
    private $auctionServices = [];

    public function __construct(AuctionServiceInterface ...$auctionService)
    {
        $this->auctionServices[] = $auctionService;
    }

    /**
     * @return AuctionOverviewInterface[]
     */
    public function getAuctionsOverviews(): array
    {
        $auctionOverviews = [];

        foreach ($this->auctionServices as $auctionService) {
            array_merge($auctionOverviews, $auctionService->getAuctionsOverviews());
        }

        return $auctionOverviews;
    }
}
