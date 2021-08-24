<?php

namespace Polly\ORM;


class EntityCache
{
    private ?array $entities = null;

    public function add($id, AbstractEntity &$object)
    {
        $this->getEntities()[$id] = $object;
    }

    public function &getEntities()
    {
        if(is_null($this->entities))
            $this->entities = [];

        return $this->entities;
    }

    public function delete($id)
    {
        unset($this->getEntities()[$id]);
    }

    public function get(string $id) : ?AbstractEntity
    {
         return $this->getEntities()[$id] ?? null;
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