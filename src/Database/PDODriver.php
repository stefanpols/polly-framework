<?php

namespace Polly\Database;

use PDO;
use PDOException;
use Polly\Core\App;
use Polly\Core\Logger;
use Polly\Interfaces\IDatabaseConnection;

class PDODriver implements IDatabaseConnection
{
    private ?PDO $pdo = null;
    private bool $connected = false;
    private string $server;
    private string $port;
    private string $database;
    private string $user;
    private string $password;

    public function __construct($databaseConfig)
    {
        $this->server   = $databaseConfig['server'];
        $this->port     = $databaseConfig['port'];
        $this->database = $databaseConfig['database'];
        $this->user     = $databaseConfig['user'];
        $this->password = $databaseConfig['password'];
    }

    public function disconnect(): bool
    {
        $this->pdo = null;
        $this->connected = false;

        return true;
    }

    public function connect(): bool
    {
        $connectionString = 'mysql:host='.$this->server.';port='.$this->port.';dbname='.$this->database;

        try
        {
            $this->pdo = new PDO($connectionString, $this->user,$this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

            # We can now log any exceptions on Fatal error.
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            # Disable emulation of prepared statements, use REAL prepared statements instead.
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

            $this->connected = true;

            return true;
        }
        catch(PDOException $e)
        {
            App::handleException($e);
        }

        return false;
    }

    public function close()
    {
        $this->pdo = null;
    }

    public function fetchAll(string $query, array $parameters=[], int $mode=PDO::FETCH_ASSOC) : array
    {
        if($this->execute($query, $parameters, $statement))
        {
            return $statement->fetchAll($mode);
        }
        return [];
    }

    public function execute(string $query, array $parameters=[], &$statement=null) : bool
    {
        $statement = $this->pdo->prepare($query);
        foreach($parameters as $param => &$var)
            $statement->bindParam($param, $var);

        try
        {
            return $statement->execute();
        }
        catch(PDOException $e)
        {
            if(App::isDebug())
                print_r($e);

            Logger::error(Logger::createFromException($e));
            return false;
        }
    }

    public function fetchSingle(string $query, array $parameters=[], int $mode=PDO::FETCH_ASSOC) : array|bool
    {
        if($this->execute($query, $parameters, $statement))
        {
            return $statement->fetch($mode);
        }
        return false;
    }

    public function __wakeup()
    {
        return false;
    }

    private function __clone()
    {
        return false;
    }

}

?>
