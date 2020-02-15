<?php


namespace App\VehicleWebsite;


interface AuctionOverviewInterface
{
    public function getName(): string;

    public function getPrice(): float;

    public function getImage(): string;

    public function getDescription(): string;
}