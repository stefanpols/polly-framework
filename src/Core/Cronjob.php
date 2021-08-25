<?php

namespace App\Jobs;

abstract class Cronjob
{

    abstract public function run() : bool;

    private bool $sendResults = true;
    private array $results = [];
    private array $errors = [];

    public function __construct()
    {
    }

    /**
     * @return bool
     */
    public function sendResults(): bool
    {
        return $this->sendResults;
    }

    /**
     * @param bool $sendResults
     */
    public function setSendResults(bool $sendResults): void
    {
        $this->sendResults = $sendResults;
    }

    /**
     * @return array
     */
    public function &getResults(): array
    {
        return $this->results;
    }

    /**
     * @param array $results
     */
    public function setResults(array $results): void
    {
        $this->results = $results;
    }

    /**
     * @return array
     */
    public function &getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }





}