<?php

namespace Polly\Exceptions;

use Throwable;

class InvalidRouteGroupException extends SerializableException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "There is no base url defined under 'routing' > 'groups' in the app.config.php where the base url (partially) match '".$message."'";
        parent::__construct($message, $code, $previous);
    }
}