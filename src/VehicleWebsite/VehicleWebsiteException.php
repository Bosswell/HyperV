<?php

namespace App\VehicleWebsite;

class VehicleWebsiteException extends \Exception
{
    /**
     * @param string $type
     * @param string $provider
     * @return VehicleWebsiteException
     */
    public static function typeMappingError(string $type, string $provider)
    {
        return new self(sprintf('Could not map type [%s] to [%s] provider', $type, $provider));
    }
}
