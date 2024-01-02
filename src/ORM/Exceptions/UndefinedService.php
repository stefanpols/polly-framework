<?php

namespace Polly\ORM\Exceptions;

use Polly\ORM\RepositoryService;
use Throwable;

class UndefinedService extends ORMException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Failed to allocate RepositoryService for '".$message."' ";
        parent::__construct($message, $code, $previous);
    }
}
