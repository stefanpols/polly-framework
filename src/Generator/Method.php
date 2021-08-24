<?php

namespace Polly\Generator;

class Method
{
    const PRIVATE = "private";
    const PROTECTED = "protected";
    const PUBLIC  = "public";

    private ?string $visibility = null;
    private ?string $name = null;
    private ?array $parameters = null;
    private ?string $returnType = null;
    private ?bool $static = null;
    private ?string $body = null;
    private ?string $entity = null;

    public static function createGetter(Property $property) : Method
    {
        $method = new Method();
        $method->setName("get".ucfirst($property->getName()));
        $method->setVisibility(Method::PUBLIC);
        $method->setReturnType(($property->isNullable() ? '?' : '').$property->getType());
        $method->setBody("return \$this->".$property->getName().';');
        return $method;
    }

    public static function createSetter(Property $property) : Method
    {
        $method = new Method();
        $method->setName("set".ucfirst($property->getName()));
        $method->setVisibility(Method::PUBLIC);
        $method->getParameters()[] = new Parameter($property);
        $method->setBody("\$this->".$property->getName()." = $".$property->getName().';');
        return $method;
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

    public function addParameter(Parameter $parameter)
    {
        $this->parameters[] = $parameter;
    }

    /**
     * @return string|null
     */
    public function getReturnType(): ?string
    {
        return $this->returnType;
    }

    /**
     * @param string|null $returnType
     */
    public function setReturnType(?string $returnType): void
    {
        $this->returnType = $returnType;
    }

    /**
     * @return bool|null
     */
    public function getStatic(): ?bool
    {
        return $this->static;
    }

    /**
     * @param bool|null $static
     */
    public function setStatic(?bool $static): void
    {
        $this->static = $static;
    }

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string|null $body
     */
    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return string|null
     */
    public function getEntity(): ?string
    {
        return $this->entity;
    }

    /**
     * @param string|null $entity
     */
    public function setEntity(?string $entity): void
    {
        $this->entity = $entity;
    }



}