<?php

namespace Polly\ORM\Interfaces;

interface INamingStrategy
{

    public function classToTableName(string $className) : string;

    public function columnPrefix(string $className) : string;

    public function propertyToColumnName(string $className, string $propertyName) : string;

    public function foreignKeyToColumnName(string $className, string $propertyName) : string;

    public function primaryKeyToColumnName(string $className, string $propertyName): string;

}