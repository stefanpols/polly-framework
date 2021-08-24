<?php

namespace Polly\Exceptions;

use Throwable;

class InvalidBasePathException extends SerializableException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Base path '".$message."' given to 'App::initialize' is not a readable directory";
        parent::__construct($message, $code, $previous);
    }
}