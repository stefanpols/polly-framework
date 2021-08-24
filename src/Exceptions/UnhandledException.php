<?php

namespace Polly\Exceptions;

use Throwable;

class UnhandledException extends SerializableException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Application crashed due to unhandled exception '".$message."'";
        parent::__construct($message, $code, $previous);
    }
}