<?php

namespace Polly\ORM\Exceptions;

use Throwable;

class UndefinedPropertyException extends ORMException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Failed to translate property '".$message."' to a database column";
        parent::__construct($message, $code, $previous);
    }
}