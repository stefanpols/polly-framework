<?php

namespace Polly\Generator;

class Property
{
    const PRIVATE = "private";
    const PROTECTED = "protected";
    const PUBLIC  = "public";

    private ?string $name = null;
    private ?string $type = null;
    private ?string $visibility = null;
    private ?bool $nullable = null;
    private ?string $defaultValue = null;
    private ?array $annotations = null;

    public function __construct(string $name, string $type, string $visibility, bool $nullable)
    {
        $this->name = $name;
        $this->type = $type;
        $this->visibility = $visibility;
        $this->nullable = $nullable;
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
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    /**
     * @param string|null $visibility
     */
    public function setVisibility(?string $visibility): void
    {
        $this->visibility = $visibility;
    }

    /**
     * @return bool|null
     */
    public function isNullable(): ?bool
    {
        return $this->nullable;
    }

    /**
     * @param bool|null $nullable
     */
    public function setNullable(?bool $nullable): void
    {
        $this->nullable = $nullable;
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

    /**
     * @return array|null
     */
    public function &getAnnotations(): ?array
    {
        return $this->annotations;
    }

    /**
     * @param array|null $annotations
     */
    public function setAnnotations(?array $annotations): void
    {
        $this->annotations = $annotations;
    }




}