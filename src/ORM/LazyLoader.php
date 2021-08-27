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
            $this->closure = null;
        }
        return $this->results;
    }

    public function clear()
    {
        $this->results = null;
        $this->executed = false;
    }


}
