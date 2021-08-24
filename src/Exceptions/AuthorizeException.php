<?php

namespace Polly\Exceptions;

use Throwable;

class AuthorizeException extends SerializableException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "The current user of the context does not have permission to perform this action (".$message.").";
        parent::__construct($message, $code, $previous);
    }
}