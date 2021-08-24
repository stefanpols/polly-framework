<?php

namespace Polly\ORM\Exceptions;

use Throwable;

class UndefinedColumnException extends ORMException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Failed to translate column '".$message."' to a property";
        parent::__construct($message, $code, $previous);
    }
}