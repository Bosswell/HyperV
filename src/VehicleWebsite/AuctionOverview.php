<?php

namespace App\VehicleWebsite;


class AuctionOverview
{
    const CARS_TYPE = 'cars';
    const MOTORCYCLES_AND_ATV_TYPE = 'motorcycles-and-atv';
    const VANS_TYPE = 'vans';
    const TRUCKS_TYPE = 'trucks';
    const CONSTRUCTION_CARS_TYPE = 'construction-cars';
    const AGRICULTURAL_VEHICLES_TYPE = 'agricultural-vehicles';
    const TRAILER_TYPE = 'trailer';

    const AVAILABLE_TYPES = [
        self::CARS_TYPE,
        self::MOTORCYCLES_AND_ATV_TYPE,
        self::VANS_TYPE,
        self::TRUCKS_TYPE,
        self::CONSTRUCTION_CARS_TYPE,
        self::AGRICULTURAL_VEHICLES_TYPE,
        self::TRAILER_TYPE,
    ];

    private string $name;
    private string $price;
    private string $image;
    private string $description;
    private string $currency;
    private string $link;

    /** @var string[] */
    private array $params;

    public function __construct(string $name, string $price, ?string $image, string $description, string $currency, string $link, array $params)
    {
        $this->name = $name;
        $this->price = $price;
        $this->image = $image;
        $this->description = $description;
        $this->currency = $currency;
        $this->link = $link;
        $this->params = $params;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
