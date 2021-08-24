<?php

namespace Polly\ORM\Interfaces;

interface IReferenceType
{
    public static function createFromDb(string $dbValue);
    public function parseToDb() : mixed;
}