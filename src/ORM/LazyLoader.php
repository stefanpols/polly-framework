<?php

namespace Polly\ORM;

use Closure;

class LazyLoader
{
    private ?Closure $closure;
    private bool $executed = false;
    private mixed $results = null;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public function getResults()
    {
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
        return ['resolved'=> $this->executed ? 'true' : 'false', 'results'=>$this->results];
    }



}
