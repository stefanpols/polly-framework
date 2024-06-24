<?php

namespace Polly\Support\Locale;

use Attribute;

#[Attribute]
class Localized
{
    public string $translationKey;

    public function __construct(string $translationKey)
    {
        $this->translationKey = $translationKey;
    }
}
