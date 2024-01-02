<?php

namespace Polly\Core;

use Polly\Exceptions\DatabaseConnectionException;
use Polly\Exceptions\DatabaseNotFoundException;
use Polly\Exceptions\UnknownDatabaseDriverException;
use Polly\Interfaces\IDatabaseConnection;

class Database
{
    const DEFAULT_DB = 'system';
    private static array $connections = [];

    private function __construct() { }

    public static function default() : IDatabaseConnection
    {
        return Database::get(Database::DEFAULT_DB);
    }

    public static function get(string $name) : IDatabaseConnection
    {
        if (!isset(static::getConnections()[$name]))
        {
            throw new DatabaseNotFoundException($name);
        }

        return static::getConnections()[$name];
    }

    /**
     * @return IDatabaseConnection[]
     */
    public static function &getConnections() : array
    {
        return static::$connections;
    }

    public static function remove(string $name) : bool
    {
        if(isset(static::getConnections()[$name]))
        {
            unset(static::getConnections()[$name]);
            static::getConnections()[$name]->disconnect();
        }
        return true;
    }

    public static function prepare() : void
    {
        if(!Config::exists("db_driver"))
            throw new UnknownDatabaseDriverException();

        $dbDriver = Config::get("db_driver");

        $dbConnection = new $dbDriver( ['server'    => App::environment('DB_HOST'),
                                        'port'      => App::environment('DB_PORT'),
                                        'database'  => App::environment('DB_DATABASE'),
                                        'user'      => App::environment('DB_USERNAME'),
                                        'password'  => App::environment('DB_PASSWORD')]);

        if(!($dbConnection instanceof IDatabaseConnection))
        {
            throw new UnknownDatabaseDriverException($dbDriver);
        }

        static::add(Database::DEFAULT_DB, $dbConnection);
    }

    public static function add(string $name, IDatabaseConnection $connection)
    {
        if(isset(static::getConnections()[$name]))
        {
            return true;
        }

        if($connection->connect())
        {
            static::getConnections()[$name] = $connection;
            return true;
        }
        else
        {
            throw new DatabaseConnectionException($name);
        }
    }
}
