<?php

namespace Polly\Exceptions;

use Throwable;

class DatabaseNotFoundException extends SerializableException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Couldn't find database connection '".$message."'";
        parent::__construct($message, $code, $previous);
    }
}