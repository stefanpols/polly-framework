<?php

namespace Polly\Exceptions;

use Throwable;

class MissingConfigFileException extends SerializableException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "The required configuration file '".$message."' is missing or not accessible";
        parent::__construct($message, $code, $previous);
    }
}