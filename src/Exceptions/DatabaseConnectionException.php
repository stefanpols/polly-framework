<?php

namespace Polly\Exceptions;

use Throwable;

class DatabaseConnectionException extends SerializableException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Unable to open database connection '".$message."'";
        parent::__construct($message, $code, $previous);
    }
}