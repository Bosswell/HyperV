<?php

namespace App\Message\Crawler\AuctionDetails;

use Symfony\Component\Validator\Constraints as Assert;

abstract class AuctionDetails
{
    /**
     * @Assert\Type("bool")
     */
    private bool $active = true;

    /**
     * @Assert\GreaterThanOrEqual(1)
     */
    private int $pageNumber = 1;

    /**
     * @return  bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return int
     */
    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }
}
