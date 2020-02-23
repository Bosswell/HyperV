<?php

namespace App\Helper;

use Throwable;

final class ExceptionHelper
{
    /**
     * @param callable $fn
     * @return mixed
     */
    public static function handleExceptionAsString(callable $fn)
    {
        try {
            return $fn();
        } catch (Throwable $ex) {
            return '';
        }
    }
}