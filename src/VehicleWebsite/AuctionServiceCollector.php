<?php


namespace App\VehicleWebsite;


final class AuctionServiceCollector
{
    /** @var AuctionServiceInterface[] */
    private array $auctionServices = [];

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
