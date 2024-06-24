<?php

namespace Polly\ORM\Annotations;

use Attribute;

#[Attribute]
class LazyMany
{
    public string $foreignEntity;
    public ?string $foreignProperty = null;

    public function __construct(string $foreignEntity, ?string $foreignProperty=null)
    {
        $this->foreignEntity = $foreignEntity;
        $this->foreignProperty = $foreignProperty;
    }
}
