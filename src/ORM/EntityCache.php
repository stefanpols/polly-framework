<?php

namespace Polly\ORM;


class EntityCache
{
    private array $entities = [];

    public function add($id, AbstractEntity &$object)
    {
        $this->getEntities()[$id] = $object;
    }

    /**
     * @return AbstractEntity[]
     */
    public function &getEntities() : array
    {
        if(is_null($this->entities))
            $this->entities = [];

        return $this->entities;
    }

    public function delete($id)
    {
        if($this->exists($id))
        {
            $this->getEntities()[$id] = null;
            unset($this->getEntities()[$id]);
        }
    }

    public function &get(string $id) : ?AbstractEntity
    {
        $var = $this->getEntities()[$id] ?? null;
        return $var;
    }

    public function exists(string $id) : bool
    {
        return isset($this->getEntities()[$id]);
    }

    public function __debugInfo()
    {
        return ['cached_objects'=>count($this->getEntities())];
    }


}