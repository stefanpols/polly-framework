<?php

namespace Polly\Exceptions;

use Throwable;

class ViewNotFoundException extends SerializableException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "View '".$message."' does not exists or is not readable";
        parent::__construct($message, $code, $previous);
    }

}