<?php

namespace Polly\ORM;

use Polly\Interfaces\IDatabaseConnection;
use Polly\ORM\Exceptions\DuplicatePlaceholderException;

class QueryBuilder
{
    const SELECT = 0;
    const INSERT = 1;
    const UPDATE = 2;
    const DELETE = 3;

    private ?string $tableName = null;
    private ?string $queryMode = null;
    private ?array $selectColumns = null;
    private ?string $customQuery = null;
    private ?array $whereConditions = null;
    private ?string $whereExpr = null;
    private ?array $valuesArray = null;
    private ?array $placeholders = [];
    private bool $singleSelect = false;
    private bool $parseToEntities = true;
    private bool $parseRelations = true;
    private ?string $orderBy = null;
    private ?int $limit = null;
    private ?int $offset = null;
    private ?string $entity = null;
    private ?IDatabaseConnection $dbConnection = null;

    public static function createQuery(string $query) : self
    {
        $queryBuilder = new self();

        if(strtolower(substr($query,0, strlen("select"))) == "select")
            $queryBuilder->queryMode = QueryBuilder::SELECT;

        else if(strtolower(substr($query,0, strlen("update"))) == "update")
            $queryBuilder->queryMode = QueryBuilder::UPDATE;

        else if(strtolower(substr($query,0, strlen("delete"))) == "delete")
            $queryBuilder->queryMode = QueryBuilder::DELETE;

        else if(strtolower(substr($query,0, strlen("insert"))) == "insert")
            $queryBuilder->queryMode = QueryBuilder::INSERT;

        $queryBuilder->customQuery = $query;

        return $queryBuilder;
    }


    public function entityInfo(string $entity)
    {
        $this->entity = $entity;
        return $this;
    }

    public function getEntity() : string
    {
        return $this->entity;
    }


    public function isSelect()
    {
        return $this->queryMode == QueryBuilder::SELECT;
    }

    public function select(?array $columns=null) : self
    {
        $this->queryMode = QueryBuilder::SELECT;
        $this->selectColumns = $columns;
        return $this;
    }

    public function custom($query) : self
    {
        $this->customQuery = $query;
        return $this;
    }

    public function insert() : self
    {
        $this->queryMode = QueryBuilder::INSERT;
        return $this;
    }

    public function update() : self
    {
        $this->queryMode = QueryBuilder::UPDATE;
        return $this;
    }

    public function delete() : self
    {
        $this->queryMode = QueryBuilder::DELETE;
        return $this;
    }

    public function value($column, $value) : self
    {
        $this->valuesArray[$column] = $value;
        return $this;
    }

    public function single() : self
    {
        $this->singleSelect = true;
        return $this;
    }

    public function limit(int $limit, int $offset=0) : self
    {
        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    public function where($column, $value) : self
    {
        $this->whereConditions[$column] = $value;
        return $this;
    }

    public function whereExpr($whereExpr) : self
    {
        if(!empty($this->whereExpr))
            $this->whereExpr .= $whereExpr;
        else
            $this->whereExpr = $whereExpr;
        return $this;
    }

    public function table(string $tableName) : self
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function orderBy(string $orderBy) : self
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function noEntities(): self
    {
        $this->parseToEntities = false;
        return $this;
    }

    public function noRelations(): self
    {
        $this->parseRelations = false;
        return $this;
    }

    public function dbConnection(IDatabaseConnection $dbConnection): self
    {
        $this->dbConnection = $dbConnection;
        return $this;
    }

    public function makeQuery() : string
    {
        if(!empty($this->customQuery))
            return $this->customQuery;

        if($this->queryMode == QueryBuilder::SELECT)
            return $this->makeSelectQuery();

        if($this->queryMode == QueryBuilder::INSERT)
            return $this->makeInsertQuery();

        if($this->queryMode == QueryBuilder::UPDATE)
            return $this->makeUpdateQuery();

        if($this->queryMode == QueryBuilder::DELETE)
            return $this->makeDeleteQuery();

        return "";
    }

    private function makeSelectQuery() : string
    {
        $query = 'SELECT ';
        $query .= (empty($this->selectColumns) ? '*' : implode(',', $this->selectColumns));
        $query .= ' FROM '.$this->tableName;

        $this->addWhere($query);

        if(!empty($this->orderBy))
            $query .= ' ORDER BY '.$this->orderBy;

        if(!is_null($this->limit))
            $query .= ' LIMIT '.$this->limit;

        if(!is_null($this->offset))
            $query .= ' OFFSET '.$this->offset;

        return $query;
    }

    private function addWhere(&$query)
    {
        if(!empty($this->whereConditions))
        {
            $query .= ' WHERE ';

            foreach($this->whereConditions as $key => $value)
            {
                if($value === null)
                {
                    $query .= $key.' IS NULL';
                }
                else
                {
                    $query .= $key.' = :'.$key;
                    $this->addPlaceholder($key, $value);
                }
                if($key != array_key_last($this->whereConditions)) $query .= ' AND ';
            }
        }
        if(!empty($this->whereExpr))
        {
            $query .= (empty($this->whereConditions) ? ' WHERE ' : " ").$this->whereExpr;
        }
    }

    public function addPlaceholder(string $key, mixed $value) : self
    {
        if(isset($this->placeholders[$key]))
        {
            throw new DuplicatePlaceholderException($key);
        }
        $this->placeholders[$key] = $value;
        return $this;
    }

    private function makeInsertQuery() : string
    {
        $query = 'INSERT INTO ';
        $query .= $this->tableName;
        $query .= '('.implode(',', array_keys($this->valuesArray)).') ';
        $query .= 'VALUES (';

        foreach($this->valuesArray as $key => $value)
        {
            $query .= ':'.$key;
            $this->addPlaceholder($key, $value);
            if($key != array_key_last($this->valuesArray))  $query .= ', ';
        }

        $query .= ')';

        return $query;
    }

    private function makeUpdateQuery() : string
    {
        $query = 'UPDATE ';
        $query .= $this->tableName;
        $query .= ' SET ';

        foreach($this->valuesArray as $key => $value)
        {
            $query .= $key.' = :'.$key;
            $this->addPlaceholder($key, $value);
            if($key != array_key_last($this->valuesArray)) $query .= ', ';
        }

        $this->addWhere($query);


        return $query;
    }

    private function makeDeleteQuery() : string
    {
        $query = 'DELETE FROM ';
        $query .= $this->tableName;

        $this->addWhere($query);

        return $query;
    }

    /**
     * @return string|null
     */
    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    /**
     * @param string|null $tableName
     */
    public function setTableName(?string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * @return string|null
     */
    public function getQueryMode(): ?string
    {
        return $this->queryMode;
    }

    /**
     * @param string|null $queryMode
     */
    public function setQueryMode(?string $queryMode): void
    {
        $this->queryMode = $queryMode;
    }

    /**
     * @return array|null
     */
    public function getSelectColumns(): ?array
    {
        return $this->selectColumns;
    }

    /**
     * @param array|null $selectColumns
     */
    public function setSelectColumns(?array $selectColumns): void
    {
        $this->selectColumns = $selectColumns;
    }

    /**
     * @return array
     */
    public function &getWhereConditions(): array
    {
        if(is_null($this->whereConditions))
            $this->whereConditions = [];
        return $this->whereConditions;
    }

    /**
     * @param array|null $whereConditions
     */
    public function setWhereConditions(?array $whereConditions): void
    {
        $this->whereConditions = $whereConditions;
    }

    /**
     * @return array
     */
    public function &getPlaceholders(): array
    {
        if(is_null($this->placeholders))
            $this->placeholders = [];

        return $this->placeholders;
    }

    /**
     * @return bool
     */
    public function isSingleSelect(): bool
    {
        return $this->singleSelect;
    }

    /**
     * @param bool $singleSelect
     */
    public function setSingleSelect(bool $singleSelect): void
    {
        $this->singleSelect = $singleSelect;
    }

    /**
     * @return bool
     */
    public function toEntities(): bool
    {
        return $this->parseToEntities;
    }

    /**
     * @return bool
     */
    public function toRelations(): bool
    {
        return $this->parseRelations;
    }

    /**
     * @return bool
     */
    public function getDbConnection(): ?IDatabaseConnection
    {
        return $this->dbConnection;
    }





}
