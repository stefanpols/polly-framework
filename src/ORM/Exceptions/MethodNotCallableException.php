<?php

namespace Polly\ORM\Exceptions;

use Throwable;

class MethodNotCallableException extends ORMException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "The given method '".$message."' is not callable on the service or repository. Make sure to implement the method.";
        parent::__construct($message, $code, $previous);
    }
}
