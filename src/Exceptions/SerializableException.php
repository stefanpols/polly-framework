<?php

namespace Polly\Exceptions;

use Exception;
use JsonSerializable;

abstract class SerializableException extends Exception implements JsonSerializable
{
    public function jsonSerialize() : mixed
    {
        return $this->getMessage();
    }
}
