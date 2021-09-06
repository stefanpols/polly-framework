<?php

namespace Polly\ORM;

use Polly\ORM\Annotations\Id;
use ReflectionObject;

abstract class AbstractEntity
{
    const PK_AUTO_INCREMENT = "AI";
    const PK_UUID = "UUID";

    #[Id]
    protected ?string $id = null;
    protected ?array $errors = null;

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function populate(array $data)
    {
        foreach($data as $key => $value)
        {
            $setter = 'set'.ucfirst($key);
            if(is_callable(array($this, $setter)))
            {
                $this->$setter($value);
            }
        }
    }

    /**
     * @return array|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * @param array|null $errors
     */
    public function setErrors(?array $errors): void
    {
        $this->errors = !empty($errors) ? $errors : null;
    }


    /**
     * Reset the cached lazy loading objects
     */
    public function clearCache()
    {
        $reflection = new ReflectionObject($this);

        foreach ($reflection->getProperties() as $property)
        {
            if($property->getType()->getName() == LazyLoader::class)
            {
                $property->setAccessible(true);
                $property->getValue($this)->clear();

            }
        }

        return $this;
    }

}
