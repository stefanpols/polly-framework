<?php

namespace Polly\Generator;

class Parameter
{
    private Property $property;
    private ?string $defaultValue = null;

    public function __construct(Property $property, ?string $defaultValue = null)
    {
        $this->property = $property;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return Property
     */
    public function getProperty(): Property
    {
        return $this->property;
    }

    /**
     * @param Property $property
     */
    public function setProperty(Property $property): void
    {
        $this->property = $property;
    }

    /**
     * @return string|null
     */
    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    /**
     * @param string|null $defaultValue
     */
    public function setDefaultValue(?string $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }


}