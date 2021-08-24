<?php

namespace Polly\ORM\Exceptions;

use Throwable;

class UndefinedRepository extends ORMException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Failed to allocate EntityRepository for '".$message."' ";
        parent::__construct($message, $code, $previous);
    }
}