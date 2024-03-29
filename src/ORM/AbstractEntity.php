<?php

namespace Polly\ORM;

use Polly\ORM\Annotations\Entity;
use Polly\ORM\Annotations\Id;
use ReflectionClass;
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
                $value = is_object($value) || is_array($value) || strlen($value) > 0 ? $value : null;
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

    public function addError($key, $message) : void
    {
        $this->errors[$key] = $message;
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

        $reflection = new ReflectionClass($this);


        foreach ($reflection->getProperties() as $property)
        {
            if($property->getType()->getName() == LazyLoader::class)
            {
                $property->setAccessible(true);
                $property->setValue($this,null);

            }
        }

        $entityAttribute = $reflection->getAttributes(Entity::class);
        $entityAttribute = array_shift($entityAttribute);
        $entityAttribute = $entityAttribute->newInstance();
        $repositoryServiceClass = $entityAttribute->repositoryServiceClass;
        $repositoryServiceClass::getRepository()->getCache()->delete($this->getId());

        EntityManager::handleEntityRelations($repositoryServiceClass::getRepository(), $this);

        return $this;
    }

}
