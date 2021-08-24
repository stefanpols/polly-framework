<?php

namespace Polly\Exceptions;

use Throwable;

class UnknownExceptionHandlerException extends SerializableException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "The given exception handlers type '".$message."' is unknown";
        parent::__construct($message, $code, $previous);
    }
}