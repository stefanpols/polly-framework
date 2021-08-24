<?php

namespace Polly\ORM\Exceptions;

use Throwable;

class DuplicatePlaceholderException extends ORMException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "The given placeholder '".$message."' is already known";
        parent::__construct($message, $code, $previous);
    }
}