<?php

namespace Polly\ORM\Annotations;


use Attribute;

#[Attribute]
class Entity
{
    public ?string $repositoryServiceClass = null;
    public ?string $primaryKey = null;
    public ?string $primaryKeyType = null;

    public function __construct(string $repositoryServiceClass, ?string $primaryKeyType=null, ?string $primaryKey=null)
    {
        $this->repositoryServiceClass = $repositoryServiceClass;
        $this->primaryKeyType = $primaryKeyType;
        $this->primaryKey = $primaryKey;
    }
}
