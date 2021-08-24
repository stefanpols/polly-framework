<?php

namespace Polly\Exceptions;

use Throwable;

class EmptyAuthorizationHandlerException extends SerializableException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "The authorization handler for the current routing group is empty, but the requested path required authorization";
        parent::__construct($message, $code, $previous);
    }
}