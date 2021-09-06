<?php

namespace Polly\ORM\Types;

use DateTimeZone;
use JsonSerializable;
use Polly\ORM\Interfaces\IReferenceType;

class DateTime extends \DateTime implements IReferenceType, JsonSerializable
{
    const MYSQL_FORMAT = "Y-m-d H:i:s";
    const SERIALIZED_ZONE = "UTC";

    public function __construct($datetime = 'now', DateTimeZone $timezone = null)
    {
        parent::__construct($datetime, $timezone);
    }

    public static function createFromDb(?string $dbValue)
    {
        if(is_null($dbValue)) return null;
        return new DateTime($dbValue, new DateTimeZone(self::SERIALIZED_ZONE));
    }

    public function parseToDb() : string
    {
        $this->setTimezone(new DateTimeZone(self::SERIALIZED_ZONE));
        return $this->format(self::MYSQL_FORMAT);
    }

    public function jsonSerialize()
    {
        return $this->setTimezone(new DateTimeZone("UTC"))->format('Y-m-d\TH:i:sO');
    }
}
