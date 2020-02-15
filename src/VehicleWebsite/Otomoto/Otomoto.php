<?php


namespace App\VehicleWebsite\Otomoto;


use App\VehicleWebsite\AuctionServiceInterface;


class Otomoto implements AuctionServiceInterface
{
    /** @var OtomotoClient */
    private $otomotoClient;

    public function __construct(OtomotoClient $otomotoClient)
    {
        $this->otomotoClient = $otomotoClient;
    }

    public function getServiceName(): string
    {
        return self::class;
    }

    public function getAuctionsOverviews(): array
    {
        $this->otomotoClient->getAuctionsOverviews()
    }
}
