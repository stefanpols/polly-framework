<?php

namespace Polly\Exceptions;

use Throwable;

class EmptyAuthenticationHandlerException extends SerializableException
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = "The authentication handler for the current routing group is empty, but the requested path required authentication";
        parent::__construct($message, $code, $previous);
    }
}
