<?php

namespace Polly\Support\ORM;


use Polly\Helpers\Str;
use Polly\ORM\Interfaces\INamingStrategy;

class DefaultNamingStrategy implements INamingStrategy
{
    public function propertyToColumnName(string $className, string $propertyName): string
    {
        return Str::toSnakeCase($propertyName);
    }

    public function primaryKeyToColumnName(string $className, string $propertyName): string
    {
        return $this->classToTableName($className).'_'.Str::toSnakeCase($propertyName);
    }

    public function classToTableName(string $className): string
    {
        return Str::toSnakeCase($className);
    }

    public function foreignKeyToColumnName(string $className, string $propertyName): string
    {
        return Str::toSnakeCase($propertyName);
    }

    public function columnPrefix(string $className): string
    {
        return '';
    }

}