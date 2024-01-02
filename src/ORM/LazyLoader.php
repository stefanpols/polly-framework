<?php

namespace Polly\ORM;

use Closure;
use Polly\ORM\Exceptions\MethodNotCallableException;

class LazyLoader
{
    private ?Closure $closure;
    private bool $executed = false;
    private mixed $results = null;

    public $data = [];

    public function __construct(?Closure $closure=null)
    {
        $this->closure = $closure;
    }

    public static function prepared(array $data)
    {
        $lazyloader = new LazyLoader(null);
        $lazyloader->data = $data;
        return $lazyloader;
    }

    public function handleLazyOne()
    {
        if(!$this->executed)
        {
            $repo = EntityManager::getRepository($this->data['entity']);

            $this->executed = true;
            $this->results = EntityManager::executeQueryBuilder(EntityManager::getRepository($this->data['entity']), (new QueryBuilder())
                ->table($repo->getTableName())
                ->select()
                ->single()
                ->where($this->data['fk'], $this->data['v']));
        }
        return $this->results;
    }

    public function handleLazyMany()
    {
        if(!$this->executed)
        {
            $this->executed = true;

            $repo = EntityManager::getRepository($this->data['entity']);
            $service = EntityManager::getService($this->data['entity']);

            $overrideMethod             = "find".$this->data['prefix']."By".ucfirst($this->data['fp']);
            $overrideMethodForService   = "find".$this->data['prefix']."By".ucfirst($this->data['source']);

            //Check if there is a method in the service to retrieve the data
            if(is_callable(array($service, $overrideMethodForService)))
            {
                $this->results = $service->$overrideMethodForService($this->data['e']);
            }
            //Check if there is a method in the repository to retrieve the data
            else if(is_callable(array($repo, $overrideMethod)))
            {
                $this->results = $repo->$overrideMethod($this->data['v']);
            }
            //Else create the default QueryBuilder
            else
            {
                if(!empty($this->data['prefix']))
                {
                    throw new MethodNotCallableException("Repository: ".$overrideMethod." | Service: ".$overrideMethodForService);
                }
                $this->results = EntityManager::executeQueryBuilder(EntityManager::getRepository($this->data['entity']), (new QueryBuilder())
                    ->table($repo->getTableName())
                    ->select()
                    ->orderBy($repo->getDefaultOrderBy())
                    ->where($this->data['fk'], $this->data['v']));
            }
        }
        return $this->results;
    }

    public function getResults()
    {
        if($this->closure == null && $this->data['type'] == 'LazyOne')
        {
            return $this->handleLazyOne();
        }
        if($this->closure == null && $this->data['type'] == 'LazyMany')
        {
            return $this->handleLazyMany();
        }

        if(!$this->executed)
        {
            $this->executed = true;
            $this->results = $this->closure->call($this);
        }
        return $this->results;
    }

    public function clear()
    {
        $this->results = null;
        $this->executed = false;
    }

    public function __debugInfo()
    {
       // return ['resolved'=> $this->executed ? 'true' : 'false', 'results'=>$this->results];
        return ['resolved'=> $this->executed ? 'true' : 'false', 'resultCount'=>count((is_array($this->results) ? $this->results : []))];
    }



}
