<?php

namespace Polly\Exceptions;

use Throwable;

class MissingConfigKeyException extends SerializableException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "The required configuration key '".$message."' is missing in the app.config.php file";
        parent::__construct($message, $code, $previous);
    }
}