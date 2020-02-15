<?php


namespace App\VehicleWebsite;

use App\VehicleWebsite\ValueObject\Detail;
use DateTime;

interface AuctionInterface
{
    public function getName(): string;

    public function getDescription(): string;

    public function getPrice(): float;

    /** @return Detail[] */
    public function getDetails(): array;

    public function getImages(): array;

    public function getAddress(): string;

    public function getLink(): string;

    public function getExperienceDate(): DateTime;
}
