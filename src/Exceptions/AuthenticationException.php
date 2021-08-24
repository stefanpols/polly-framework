<?php

namespace Polly\Exceptions;

use Throwable;

class AuthenticationException extends SerializableException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Could not authenticate, but the invoked action required authentication";
        parent::__construct($message, $code, $previous);
    }
}