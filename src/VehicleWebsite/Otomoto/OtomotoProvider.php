<?php

namespace App\VehicleWebsite\Otomoto;

use App\Message\Crawler\AuctionDetails\OtomotoDetails;
use App\Message\Crawler\CrawlAuctionsOverviewsMessage;
use App\VehicleWebsite\AuctionOverview;
use App\VehicleWebsite\AuctionServiceInterface;
use App\VehicleWebsite\VehicleWebsiteException;
use Throwable;

class OtomotoProvider implements AuctionServiceInterface
{
    private OtomotoClient $otomotoClient;
    private OtomotoDetails $otomotoDetails;

    public function __construct(OtomotoClient $otomotoClient)
    {
        $this->otomotoClient = $otomotoClient;
    }

    /**
     * @param CrawlAuctionsOverviewsMessage $crawlAuctionsOverviewsMessage
     * @return AuctionOverview[]
     * @throws Throwable
     */
    public function getAuctionsOverviews(CrawlAuctionsOverviewsMessage $crawlAuctionsOverviewsMessage): array
    {
        $this->otomotoDetails = $crawlAuctionsOverviewsMessage->getOtomotoDetails();

        if (!$this->otomotoDetails->isActive()) {
            return [];
        }

        return $this->otomotoClient->getAuctionsOverviews(
            $this->normalizeType($crawlAuctionsOverviewsMessage),
            $crawlAuctionsOverviewsMessage->getSearchQuery(),
            $this->otomotoDetails->getPageNumber()
        );
    }

    /**
     * @param CrawlAuctionsOverviewsMessage $crawlAuctionsOverviewsMessage
     * @return string
     * @throws VehicleWebsiteException
     */
    private function normalizeType(CrawlAuctionsOverviewsMessage $crawlAuctionsOverviewsMessage): string
    {
        switch ($crawlAuctionsOverviewsMessage->getType()) {
            case AuctionOverview::CARS_TYPE:
                return 'osobowe';
            case AuctionOverview::MOTORCYCLES_AND_ATV_TYPE:
                return 'motocykle-i-quady';
            case AuctionOverview::VANS_TYPE:
                return 'dostawcze';
            case AuctionOverview::TRUCKS_TYPE:
                return 'ciezarowe';
            case AuctionOverview::CONSTRUCTION_CARS_TYPE:
                return 'budowlane';
            case AuctionOverview::TRAILER_TYPE:
                return 'przyczepy';
            case AuctionOverview::AGRICULTURAL_VEHICLES_TYPE:
                return 'rolnicze';
        }

        throw VehicleWebsiteException::typeMappingError($crawlAuctionsOverviewsMessage->getType(), self::class);
    }
}
