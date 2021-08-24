<?php

namespace Polly\ORM\Exceptions;

use Throwable;

class InvalidEntityClassException extends ORMException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "The given entity class '".$message."' is not valid. Make sure it extends from 'Polly\ORM\Entity'";
        parent::__construct($message, $code, $previous);
    }
}