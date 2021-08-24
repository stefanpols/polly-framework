<?php

namespace Polly\ORM\Exceptions;

use Throwable;

class UnknownRelationException extends ORMException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Failed to handle relation '".$message."' ";
        parent::__construct($message, $code, $previous);
    }
}