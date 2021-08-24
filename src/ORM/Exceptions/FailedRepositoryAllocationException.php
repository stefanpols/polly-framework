<?php

namespace Polly\ORM\Exceptions;

use Throwable;

class FailedRepositoryAllocationException extends ORMException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Failed to allocate repository for '".$message."' ";
        parent::__construct($message, $code, $previous);
    }
}