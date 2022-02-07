<?php

namespace Polly\ORM;

use Exception;
use Polly\Helpers\UUID;
use Polly\Interfaces\IDatabaseConnection;
use Polly\ORM\Annotations\AutoSortAsc;
use Polly\ORM\Annotations\AutoSortDesc;
use Polly\ORM\Annotations\Entity;
use Polly\ORM\Annotations\ForeignId;
use Polly\ORM\Annotations\Id;
use Polly\ORM\Annotations\LazyMany;
use Polly\ORM\Annotations\LazyOne;
use Polly\ORM\Annotations\ReadOnly;
use Polly\ORM\Annotations\Variable;
use Polly\ORM\Exceptions\UndefinedColumnException;
use Polly\ORM\Exceptions\UndefinedPropertyException;
use Polly\ORM\Interfaces\INamingStrategy;
use Polly\ORM\Interfaces\IReferenceType;
use Polly\ORM\Validation\Domain;
use Polly\ORM\Validation\Email;
use Polly\ORM\Validation\Ip;
use Polly\ORM\Validation\NotEmpty;
use Polly\ORM\Validation\Unique;
use Polly\ORM\Validation\Url;
use ReflectionClass;

abstract class EntityRepository
{
    private ?string $entity;
    private ?string $entityClassName;
    private ?IDatabaseConnection $connection;
    private ?string $tableName = null;
    private ?array $relations = null;
    private ?array $validators = null;
    private ?array $referenceTypeProperties = null;
    private ?array $dbMapping = null;
    private ?array $readMapping = null;
    private ?EntityCache $cache = null;
    private string $primaryKey = 'id';
    private string $defaultOrderBy = '';
    private ?string $primaryKeyType = null;

    protected function __construct(string $entity, IDatabaseConnection $connection)
    {
        $this->entity           = $entity;
        $this->entityClassName  = (new ReflectionClass($entity))->getShortName();
        $this->connection       = $connection;

        EntityManager::addRepository($this);
    }

    public function reflectEntity(INamingStrategy $namingStrategy)
    {
        $this->tableName = $namingStrategy->classToTableName($this->entityClassName);
        $this->dbMapping = [];
        $this->readMapping = [];

        $reflection = new ReflectionClass($this->getEntity());

        $entityAttribute = $reflection->getAttributes(Entity::class);
        $entityAttribute = array_shift($entityAttribute);
        $entityAttribute = $entityAttribute->newInstance();

        $this->primaryKeyType = $entityAttribute->primaryKeyType ?? EntityManager::getDefaultPrimaryKeyType();
        $reflection = new ReflectionClass($this->getEntity());
        foreach ($reflection->getProperties() as $property)
        {
            try
            {
                $type = $property->getType()->getName();

                $reflection = new ReflectionClass($type);
                $instance = $reflection->newInstanceWithoutConstructor();
                if($instance instanceof IReferenceType)
                {
                    $this->referenceTypeProperties[$property->name] = $property->getType()->getName();
                }
            }
            catch(Exception)
            {
                //Its ok, if reflect failed it was definitely not an instance of IReferenceType
            }

            foreach ($property->getAttributes() as $attribute)
            {
                $attribute = $attribute->newInstance();

                //Reflect properties
                if ($attribute instanceof ReadOnly)
                {
                    $this->readMapping[$property->name] = $namingStrategy->propertyToColumnName($this->entityClassName, $property->name);
                }
                else if ($attribute instanceof Variable)
                {
                    $this->dbMapping[$property->name] = $namingStrategy->propertyToColumnName($this->entityClassName, $property->name);
                    $this->readMapping[$property->name] = $namingStrategy->propertyToColumnName($this->entityClassName, $property->name);
                }
                elseif ($attribute instanceof ForeignId)
                {
                    $this->dbMapping[$property->name] = $namingStrategy->foreignKeyToColumnName($this->entityClassName, $property->name);
                    $this->readMapping[$property->name] = $namingStrategy->foreignKeyToColumnName($this->entityClassName, $property->name);
                }
                elseif ($attribute instanceof Id)
                {
                    $this->dbMapping[$property->name] = $namingStrategy->primaryKeyToColumnName($this->entityClassName, $property->name);
                    $this->readMapping[$property->name] = $namingStrategy->primaryKeyToColumnName($this->entityClassName, $property->name);
                }

                //Reflect relations
                elseif ($attribute instanceof LazyMany || $attribute instanceof LazyOne)
                    $this->relations[$property->name] = $attribute;

                //Reflect auto sorting
                elseif ($attribute instanceof AutoSortDesc)
                    $this->defaultOrderBy = $this->getColumnName($property->name) . ' DESC';

                elseif ($attribute instanceof AutoSortAsc)
                    $this->defaultOrderBy = $this->getColumnName($property->name) . ' ASC';

                //Reflect validation
                elseif (in_array($attribute::class, [NotEmpty::class, Email::class, Ip::class, Domain::class, Url::class, Unique::class]))
                    $this->validators[$property->name][] = $attribute;
            }

        }

    }

    /**
     * @return string|null
     */
    public function getEntity(): ?string
    {
        return $this->entity;
    }

    /**
     * @param string|null $entity
     */
    public function setEntity(?string $entity): void
    {
        $this->entity = $entity;
    }

    public function getColumnName(string $propertyName) : string
    {
        if(!isset($this->readMapping[$propertyName]))
        {
            throw new UndefinedPropertyException($propertyName.' ('.$this->getEntity().')');
        }


        return $this->readMapping[$propertyName];
    }

    public function getPropertyName(string $columnName) : string
    {
        $columnKey = array_search($columnName, $this->getReadMapping());

        if($columnKey === false)
            throw new UndefinedColumnException($columnName);

        return $columnKey;
    }

    /**
     * @return array|null
     */
    public function getDbMapping(): ?array
    {
        return $this->dbMapping;
    }

    /**
     * @param array|null $dbMapping
     */
    public function setDbMapping(?array $dbMapping): void
    {
        $this->dbMapping = $dbMapping;
    }

    /**
     * @return array|null
     */
    public function getReadMapping(): ?array
    {
        return $this->readMapping;
    }

    /**
     * @param array|null $readMapping
     */
    public function setReadMapping(?array $readMapping): void
    {
        $this->readMapping = $readMapping;
    }


    public function all() : array
    {
        $queryBuilder = (new QueryBuilder())
            ->table($this->getTableName())
            ->select()
            ->orderBy($this->defaultOrderBy);

        return $this->execute($queryBuilder);
    }

    public function limited(int $limit, int $offset=0) : array
    {
        $queryBuilder = (new QueryBuilder())
            ->table($this->getTableName())
            ->select()
            ->limit($limit, $offset)
            ->orderBy($this->defaultOrderBy);

        return $this->execute($queryBuilder);
    }

    public function count() : array
    {
        $queryBuilder = (new QueryBuilder())
            ->table($this->getTableName())
            ->select(["COUNT(*) as count"])
            ->single()
            ->noEntities();

        return $this->execute($queryBuilder);
    }

    /**
     * @return string|null
     */
    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName) : void
    {
        $this->tableName = $tableName;
    }

    public function execute(QueryBuilder $queryBuilder) : mixed
    {
        return EntityManager::executeQueryBuilder($this, $queryBuilder);
    }

    public function find(string $id) : ?AbstractEntity
    {
        $queryBuilder = (new QueryBuilder())
            ->table($this->getTableName())
            ->select()
            ->single()
            ->where($this->getColumnName($this->getPrimaryKey()), $id);

        return $this->execute($queryBuilder);
    }

    /**
     * @return string|null
     */
    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     */
    public function setPrimaryKey(string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }

    public function delete(AbstractEntity $entity, ?string $table = null) : bool
    {
        $queryBuilder = (new QueryBuilder())
            ->table($table ?? $this->getTableName())
            ->delete()
            ->where($this->getColumnName($this->getPrimaryKey()), $entity->getId());

        return $this->execute($queryBuilder);
    }

    public function allWhere(string $property, string $value) : array
    {
        $queryBuilder = (new QueryBuilder())
            ->table($this->getTableName())
            ->select()
            ->orderBy($this->defaultOrderBy)
            ->where($this->getColumnName($property), $value);

        return $this->execute($queryBuilder);
    }

    public function singleWhere(string $property, string $value) : array
    {
        $queryBuilder = (new QueryBuilder())
            ->table($this->getTableName())
            ->select()
            ->single()
            ->where($this->getColumnName($property), $value);

        return $this->execute($queryBuilder);
    }

    public function save(AbstractEntity $entity, ?string $table = null) : bool
    {
        if(is_null($entity->getId()))
            return $this->insert($entity, $table);
        else
            return $this->update($entity, $table);
    }

    public function insert(AbstractEntity $entity, ?string $table = null) : bool
    {
        $queryBuilder = (new QueryBuilder())
            ->table($table ?? $this->getTableName())
            ->insert();

        $this->mapQueryBuilder($queryBuilder, $entity);

        if($this->getPrimaryKeyType() == AbstractEntity::PK_UUID)
        {
            if(empty($entity->getId()))
                $entity->setId(UUID::v4());
            $queryBuilder->value($this->getColumnName($this->getPrimaryKey()), $entity->getId());
        }

        $succeeded = $this->execute($queryBuilder);

        if($succeeded && $this->getPrimaryKeyType() == AbstractEntity::PK_AUTO_INCREMENT)
        {
            $entity->setId($this->getConnection()->lastInsertId());
        }

        return $succeeded;
    }

    public function mapQueryBuilder(&$queryBuilder, &$entity, bool $skipPrimary=false)
    {
        foreach($this->getDbMapping() as $property=>$column)
        {
            if($property == $this->getPrimaryKey()) continue;

            $getter = "get".ucfirst($property);

            $referenceType = $this->getReferenceTypeProperties()[$property] ?? null;
            if($referenceType)
            {
                $queryBuilder->value($column, $entity->$getter()?->parseToDb() ?? null);
            }
            else
            {
                //Its a primitive type
                $queryBuilder->value($column, $entity->$getter());
            }
        }
    }

    /**
     * @return IReferenceType[]|null
     */
    public function getReferenceTypeProperties(): ?array
    {
        return $this->referenceTypeProperties;
    }

    /**
     * @param IReferenceType[]|null $referenceTypeProperties
     */
    public function setReferenceTypeProperties(?array $referenceTypeProperties): void
    {
        $this->referenceTypeProperties = $referenceTypeProperties;
    }

    /**
     * @return string|null
     */
    public function getPrimaryKeyType(): ?string
    {
        return $this->primaryKeyType;
    }

    /**
     * @param string|null $primaryKeyType
     */
    public function setPrimaryKeyType(?string $primaryKeyType): void
    {
        $this->primaryKeyType = $primaryKeyType;
    }

    public function update(AbstractEntity $entity, ?string $table = null)
    {
        $queryBuilder = (new QueryBuilder())
            ->table($table ?? $this->getTableName())
            ->update();

        $this->mapQueryBuilder($queryBuilder, $entity, true);

        $queryBuilder->where($this->getColumnName($this->getPrimaryKey()), $entity->getId());

        return $this->execute($queryBuilder);
    }

    /**
     * @return string|null
     */
    public function getEntityClassName(): ?string
    {
        return $this->entityClassName;
    }

    /**
     * @return IDatabaseConnection|null
     */
    public function getConnection(): ?IDatabaseConnection
    {
        return $this->connection;
    }

    /**
     * @param IDatabaseConnection|null $connection
     */
    public function setConnection(?IDatabaseConnection $connection): void
    {
        $this->connection = $connection;
    }

    public function &getRelations(): array
    {
        if(is_null($this->relations)) $this->relations = [];
        return $this->relations;
    }

    /**
     * @param array|null $relations
     */
    public function setRelations(?array $relations): void
    {
        $this->relations = $relations;
    }

    /**
     * @return EntityCache|null
     */
    public function &getCache(): ?EntityCache
    {
        return $this->cache;
    }

    /**
     * @param EntityCache|null $cache
     */
    public function setCache(?EntityCache $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @return string
     */
    public function getDefaultOrderBy(): string
    {
        return $this->defaultOrderBy;
    }

    /**
     * @param string $defaultOrderBy
     */
    public function setDefaultOrderBy(string $defaultOrderBy): void
    {
        $this->defaultOrderBy = $defaultOrderBy;
    }

    /**
     * @return array|null
     */
    public function getValidators(): ?array
    {
        return $this->validators;
    }

    /**
     * @param array|null $validators
     */
    public function setValidators(?array $validators): void
    {
        $this->validators = $validators;
    }






}
