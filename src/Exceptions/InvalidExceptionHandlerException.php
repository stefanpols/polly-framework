<?php

namespace Polly\Exceptions;

use Throwable;

class InvalidExceptionHandlerException extends SerializableException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "The given exception handler '".$message."' is not valid. Make sure you have set the correct parameters for the handler type";
        parent::__construct($message, $code, $previous);
    }
}