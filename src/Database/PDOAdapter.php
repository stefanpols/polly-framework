<?php

namespace Polly\Database;

use PDO;
use Polly\Interfaces\IDatabaseConnection;

class PdoAdapter implements IDatabaseConnection
{
    // @object, The PDO object
    private $pdo;
    // @object, PDO statement object
    private $sQuery;
    // @array, The database settings
    private $settings;
    // @bool , Connected to the database
    private $bConnected = false;

    // @array, The parameters of the SQL query
    private $parameters;
    private $_host;
    private $_name;
    private $_user;
    private $_password;
    private $_port;

    /**
     * Default Constructor
     *
     * 1. Instantiate Log class.
     * 2. Connect to database.
     * 3. Creates the parameter array.
     */
    public function __construct($databaseConfig)
    {
        if(is_null($databaseConfig) || empty($databaseConfig))
        {
            throw new \Exception("Database config not set");
        }

        /**
         *     [class] => \SpolsMVC\Core\Modules\Db\PdoAdapter
        [host] => localhost
        [name] => riox_dev
        [user] => root
        [password] =>
         */

        $this->_host = 'localhost';
        $this->_name = $databaseConfig ['database'];
        $this->_user = $databaseConfig ['user'];
        $this->_password = $databaseConfig ['password'];
        $this->_port = $databaseConfig['port'];

        $this->parameters = array();
    }

    /**
     * This method makes connection to the database.
     *
     * 1. Reads the database settings from a ini file.
     * 2. Puts the ini content into the settings array.
     * 3. Tries to connect to the database.
     * 4. If connection failed, exception is displayed and a log file gets created.
     */
    public function connect(): bool
    {
        $dsn = 'mysql:dbname=' . $this->_name . ';host=' . $this->_host . ';port='.$this->_port;
        try
        {
            // Read settings from INI file, set UTF8
            $this->pdo = new PDO($dsn, $this->_user, $this->_password, array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

            // We can now log any exceptions on Fatal error.
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Disable emulation of prepared statements, use REAL prepared statements instead.
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            // Connection succeeded, set the boolean to true.
            $this->bConnected = true;
        }
        catch(\PDOException $e)
        {
            print_r($e);
            $this->ExceptionLog($e->getMessage());
        }

        return true;
    }

    /*
     * You can use this little method if you want to close the PDO connection
     *
     */
    public function closeConnection()
    {
        // Set the PDO object to null to close the connection
        // http://www.php.net/manual/en/pdo.connections.php
        $this->pdo = null;
    }

    /**
     * Every method which needs to execute a SQL query uses this method.
     *
     * 1. If not connected, connect to the database.
     * 2. Prepare Query.
     * 3. Parameterize Query.
     * 4. Execute Query.
     * 5. On exception : Write Exception into the log + SQL query.
     * 6. Reset the Parameters.
     */
    private function Init($query, $parameters = "")
    {
        // Connect to database
        if(! $this->bConnected)
        {
            $this->Connect();
        }
        try
        {

            // Prepare query
            $this->sQuery = $this->pdo->prepare($query);
            print_r($this->sQuery);
            print_r($parameters);
            // Add parameters to the parameter array
           // $this->bindMore($parameters);

            foreach($parameters as $param => &$var)
            {
                $this->sQuery->bindParam($param, $var);

            }


            // Execute SQL
            $time = microtime(true);
            echo "Execute: " . $query."\r\n";
            $this->succes = $this->sQuery->execute();
            echo "Execute Query Time: " . (microtime(true) - $time)."\r\n";


            // echo "\r\n".microtime(true) - $start."\r\n";
        }
        catch(\PDOException $e)
        {
            // Write into log and display Exception
            $this->ExceptionLog($e->getMessage(), $query);
            //throw new DatabaseQueryException();
        }
    }

    /**
     * @void
     *
     * Add the parameter to the parameter array
     *
     * @param string $para
     * @param string $value
     */
    public function bind($para, $value)
    {
        $this->parameters [sizeof($this->parameters)] = ":" . $para . "[%#SPOLSMVC#$]" . $value;
    }

    /**
     * @void
     *
     * Add more parameters to the parameter array
     *
     * @param array $parray
     */
    public function bindMore($parray)
    {
        if(empty($this->parameters) && is_array($parray))
        {
            $columns = array_keys($parray);
            foreach($columns as $i => &$column)
            {
                $this->bind($column, $parray [$column]);
            }
        }
    }

    /**
     * If the SQL query contains a SELECT or SHOW statement it returns an array containing all of the result set row
     * If the SQL statement is a DELETE, INSERT, or UPDATE statement it returns the number of affected rows
     *
     * @param string $query
     * @param array $params
     * @param int $fetchmode
     * @return mixed
     */
    public function query($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $query = trim($query);

        $this->Init($query, $params);
        $rawStatement = explode(" ", $query);

        ////echo $this->_name.'-'.$query.'<br />';

        // Which SQL statement is used
        $statement = strtolower($rawStatement [0]);
        if($statement === 'select' || $statement === 'show')
        {
            $start = microtime(true);
            echo "Fetch all: " . $query."\r\n";

            $result = $this->sQuery->fetchAll($fetchmode);
            echo "Fetching Time ".count($result).": " . (microtime(true) - $start)."\r\n";

            return $result;
        }
        elseif($statement === 'insert' || $statement === 'update' || $statement === 'delete')
        {
            return $this->sQuery->rowCount();
        }
        else
        {
            return NULL;
        }
    }

    /**
     * Returns the last inserted id.
     *
     * @return string
     */
    public function lastInsertId(): int
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Returns an array which represents a column from the result set
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function column($query, $params = null)
    {
        $this->Init($query, $params);
        $Columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);

        $column = null;
        foreach($Columns as $cells)
        {
            $column [] = $cells [0];
        }
        return $column;
    }

    /**
     * Returns an array which represents a row from the result set
     *
     * @param string $query
     * @param array $params
     * @param int $fetchmode
     * @return array
     */
    public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $this->Init($query, $params);
        //echo $query.'<br />';
        return $this->sQuery->fetch($fetchmode);
    }

    /**
     * Returns the value of one single field/column
     *
     * @param string $query
     * @param array $params
     * @return string
     */
    public function single($query, $params = null)
    {
        $this->Init($query, $params);
        //echo $query.'<br />';
        return $this->sQuery->fetchColumn();
    }

    /**
     * Writes the log and returns the exception
     *
     * @param string $message
     * @param string $sql
     * @return string
     */
    private function ExceptionLog($message, $sql = "")
    {
        print_r($message);
        print_r($sql);
        //Logger::DatabaseError($message, $sql);
    }

    public function __sleep()
    {
        return array();
    }

    public function __wakeup()
    {
        return;
    }

    public function disconnect(): bool
    {
        // TODO: Implement disconnect() method.
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function fetchAll(string $query, array $parameters = []): array
    {
        return $this->query($query, $parameters);
    }

    public function fetchSingle(string $query, array $parameters = []): array|bool
    {
        return $this->row($query, $parameters);
    }

    public function execute(string $query, array $parameters = []): bool
    {
        return $this->query($query, $parameters);
        // TODO: Implement execute() method.
    }
}

?>
