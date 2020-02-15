<?php


namespace App\VehicleWebsite\Otomoto;

use InvalidArgumentException;


class OtomotoAuctionOverviewRequest
{
    const AVAILABLE_TYPES = [
        'osobowe',
        'czesci',
        'motocykle-i-quady',
        'dostawcze',
        'ciezarowe',
        'budowalne',
        'przyczepy',
        'rolnicze'
    ];

    /** @var @string */
    private $type;

    /** @var string */
    private $searchQuery;

    /** @var int */
    private $pageNumber;

    /** @var bool */
    private $getPromoted;

    public function __construct(string $type, string $searchQuery, int $pageNumber, bool $getPromoted)
    {
        if (!key_exists($type, self::AVAILABLE_TYPES)) {
            throw new InvalidArgumentException(sprintf(
                'Type [%s] is not available',
                $type
            ));
        }

        $this->type = $type;
        $this->searchQuery = $searchQuery;
        $this->pageNumber = $pageNumber;
        $this->getPromoted = $getPromoted;
    }

    public function buildUrl(): string
    {
        return sprintf(
            '/%s/q-%s',
            $this->type,
            $this->searchQuery
        );
    }
}
