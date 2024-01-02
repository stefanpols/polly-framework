<?php

namespace Polly\ORM\Annotations;

use Attribute;

#[Attribute]
class LazyMany
{
    public string $foreignEntity;
    public ?string $foreignProperty = null;
    public ?string $prefix = null;

    public function __construct(string $foreignEntity, ?string $foreignProperty=null, ?string $prefix=null)
    {
        $this->foreignEntity = $foreignEntity;
        $this->foreignProperty = $foreignProperty;
        $this->prefix = $prefix;
    }
}
