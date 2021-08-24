<?php

namespace Polly\ORM\Exceptions;

use Throwable;

class NoConfigurationFoundException extends ORMException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "No Polly\ORM\EntityConfiguration found for '".$message."'. Make sure a configuration is added to the Polly\ORM\EntityManager.";
        parent::__construct($message, $code, $previous);
    }
}