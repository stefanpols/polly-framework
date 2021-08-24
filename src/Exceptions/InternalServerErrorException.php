<?php

namespace Polly\Exceptions;

use Throwable;

class InternalServerErrorException extends SerializableException
{
    public function __construct($message = "Internal server error", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}