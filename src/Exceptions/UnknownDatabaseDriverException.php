<?php

namespace Polly\Exceptions;

use Polly\Interfaces\IDatabaseConnection;
use Throwable;

class UnknownDatabaseDriverException extends SerializableException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Unknown database driver '{$message}'. Make sure 'db_driver' is added in the app.config and the target class implements ".IDatabaseConnection::class;
        parent::__construct($message, $code, $previous);
    }
}