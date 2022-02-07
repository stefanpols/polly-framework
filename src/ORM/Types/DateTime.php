<?php

namespace Polly\ORM\Types;

use DateTimeZone;
use JsonSerializable;
use Polly\ORM\Interfaces\IReferenceType;

class DateTime extends \DateTime implements IReferenceType, JsonSerializable
{
    const MYSQL_FORMAT = "Y-m-d H:i:s";
    const POST_FORMAT = "d/m/Y";
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

    public static function createFromPost(?string $postValue)
    {
        if(is_null($postValue)) return null;
        return DateTime::createFromFormat(self::POST_FORMAT, $postValue, new DateTimeZone(self::SERIALIZED_ZONE));
    }

    public static function create(string $format, string $value)
    {
        if(is_null($value)) return null;
        return DateTime::createFromFormat($format, $value, new DateTimeZone(self::SERIALIZED_ZONE));
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
