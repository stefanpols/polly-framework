<?php

namespace Polly\ORM\Annotations;

use Attribute;

#[Attribute]
class LazyOne
{
    public string $foreignEntity;
    public ?string $referenceProperty = null;

    public function __construct(string $foreignEntity, ?string $referenceProperty=null)
    {
        $this->foreignEntity = $foreignEntity;
        $this->referenceProperty = $referenceProperty;
    }
}
