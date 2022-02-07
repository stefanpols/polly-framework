<?php

namespace Polly\Interfaces;

interface IDatabaseConnection
{
    public function __construct($databaseConfig);
    public function connect() : bool;
    public function disconnect() : bool;
    public function close();
    public function fetchAll(string $query, array $parameters=[]) : array;
    public function fetchSingle(string $query, array $parameters=[]) : array|bool;
    public function execute(string $query, array $parameters=[]) : bool;
    public function lastInsertId() : int;
}
