<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ValidationException extends \Exception
{
    public function __construct($message = '', $code = Response::HTTP_UNPROCESSABLE_ENTITY, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}