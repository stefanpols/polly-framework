<?php

namespace Polly\Generator;

class Annotation
{
    private ?string $name = null;
    private ?array $parameters = null;

    public function __construct(string $name, ?array $parameters=null)
    {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array|null
     */
    public function &getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * @param array|null $parameters
     */
    public function setParameters(?array $parameters): void
    {
        $this->parameters = $parameters;
    }


}