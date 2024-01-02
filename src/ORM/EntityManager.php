<?php

namespace Polly\ORM;


use App\Models\Revision;
use Polly\Core\Config;
use Polly\ORM\Annotations\Entity;
use Polly\ORM\Annotations\LazyMany;
use Polly\ORM\Annotations\LazyOne;
use Polly\ORM\Exceptions\FailedRepositoryAllocationException;
use Polly\ORM\Exceptions\MethodNotCallableException;
use Polly\ORM\Exceptions\UndefinedRepository;
use Polly\ORM\Exceptions\UndefinedService;
use Polly\ORM\Exceptions\UnknownRelationException;
use Polly\ORM\Interfaces\INamingStrategy;
use Polly\Support\ORM\DefaultNamingStrategy;
use ReflectionClass;

class EntityManager
{
    private static ?INamingStrategy $namingStrategy = null;
    private static bool $cache = true;
    private static array $repositories = [];
    private static array $services = [];
    private static string $defaultPrimaryKeyType;

    private function __construct() { }

    public static function prepare()
    {
        $ormConfig = Config::get('orm', []);

        static::$namingStrategy         = new ($ormConfig['naming_strategy'] ?? DefaultNamingStrategy::class)();
        static::$cache                  = $ormConfig['cache'] ?? true;
        static::$defaultPrimaryKeyType  = $ormConfig['default_pk_type'] ?? AbstractEntity::PK_UUID;
    }

    public static function addRepository(EntityRepository $repository)
    {
        $repository->reflectEntity(static::$namingStrategy);
        if(static::$cache)
        {
            $repository->setCache(new EntityCache());
        }

        static::$repositories[$repository->getEntity()] = $repository;
    }


    public static function addService(RepositoryService $service)
    {
        static::$services[$service->getRepository()->getEntity()] = $service;
    }

    public static function executeQueryBuilder(EntityRepository $repository, QueryBuilder $queryBuilder) : mixed
    {
        if ($queryBuilder->isSelect())
        {
            if($repository->getCache() && $queryBuilder->isSingleSelect() && count($queryBuilder->getWhereConditions()) == 1)
            {
                $idColumn = $repository->getColumnName($repository->getPrimaryKey());
                $queryId = $queryBuilder->getWhereConditions()[$idColumn] ?? null;
                if($queryId && $repository->getCache()?->exists($queryId))
                    return $repository->getCache()->get($queryId);
            }

            $fetchMethod = ($queryBuilder->isSingleSelect()) ? 'fetchSingle' : 'fetchAll';

            $query = $queryBuilder->makeQuery();
            if($queryBuilder->getDbConnection())
            {
                $results = $queryBuilder->getDbConnection()->$fetchMethod($query, $queryBuilder->getPlaceholders());
            }
            else
            {
                $results = $repository->getConnection()->$fetchMethod($query, $queryBuilder->getPlaceholders());
            }


            if($results === false)
                return null;

            if($queryBuilder->toEntities())
            {
                return static::toEntities($repository, $results, $queryBuilder->toRelations());
            }
            else
            {
                return $results;
            }
        }
        else
        {
            return $repository->getConnection()->execute($queryBuilder->makeQuery(), $queryBuilder->getPlaceholders());
        }

    }

    public static function toEntities(EntityRepository $repository, array $data, bool $parseRelations=true)
    {
        if(empty($data)) return [];

        if(isset($data[0]))
        {
            $entities = [];
            foreach($data as $entityData)
            {
                $entity = static::createEntity($repository, $entityData, $parseRelations);
                $entities[$entity->getId()] = $entity;
            }

            return $entities;
        }

        return static::createEntity($repository, $data, $parseRelations);
    }

    public static function createEntity(EntityRepository $repository, array $entityData, bool $parseRelations=true)
    {
        $idField = $repository->getColumnName($repository->getPrimaryKey());
        $id = $entityData[$idField];

        if($repository->getCache()?->exists($id))
        {
            return $repository->getCache()->get($id);
        }

        $entity = $repository->createEntity();

        foreach($entityData as $column => $value)
        {
            $property = $repository->getPropertyName($column);
            $setter = "set".ucfirst($property);

            $referenceType = $repository->getReferenceTypeProperties()[$property] ?? null;

            if($referenceType)
            {
                $entity->$setter($referenceType::createFromDb($value));
            }
            else
            {
                //Its a primitive type
                $entity->$setter($value);
            }
        }

        if($repository->getCache())
        {
            $repository->getCache()->add($entity->getId(), $entity);
        }

        if($parseRelations)
        {
            static::handleEntityRelations($repository, $entity);
        }


        return $entity;
    }

    public static function handleEntityRelations(EntityRepository $repository, AbstractEntity &$entity)
    {
        foreach($repository->getRelations() as $property => $relation)
        {
            $foreignEntity        = $relation->foreignEntity;
            $foreignRepository    = static::getRepository($foreignEntity);
            $propertySetter       = "set".ucfirst($property);

            if ($relation instanceof LazyMany)
            {
                $prefix = "";
                if(!empty($relation->prefix))
                    $prefix = ucfirst($relation->prefix);

                $foreignProperty            = !empty($relation->foreignProperty) ? $relation->foreignProperty : lcfirst($repository->getEntityClassName()).ucfirst($repository->getPrimaryKey());
                $foreignKey = $foreignRepository->getColumnName($foreignProperty);
                $entity->$propertySetter(LazyLoader::prepared([
                    'type'=> 'LazyMany',
                    'entity'=> $foreignEntity,
                    'fp'=>$foreignProperty,
                    'source'=>$repository->getEntityClassName(),
                    'fk'=>$foreignKey,
                    'e'=>$entity,
                    'v'=>$entity->getId(),
                    'prefix'=> $prefix
                ]));
            }
            elseif ($relation instanceof LazyOne)
            {
                $referenceProperty      = $relation->referenceProperty ?? lcfirst($foreignRepository->getEntityClassName()).ucfirst($foreignRepository->getPrimaryKey());
                $foreignProperty        = $foreignRepository->getPrimaryKey();
                $foreignKey             = $foreignRepository->getColumnName($foreignProperty);
                $getter                 = "get".ucfirst($referenceProperty);

                if($entity->$getter() !== null)
                {
                    $entity->$propertySetter(LazyLoader::prepared([
                        'type'=> 'LazyOne',
                        'entity'=> $foreignEntity,
                        'fk'=>$foreignKey,
                        'v'=>$entity->$getter()
                    ]));
                }
            }
            else
            {
                throw new UnknownRelationException($relation::class);
            }
        }
    }

    public static function &getRepository(string $entity) : EntityRepository
    {
        try
        {
            if(!isset(static::$repositories[$entity]))
                throw new UndefinedRepository($entity);

            return static::$repositories[$entity];
        }
        catch(UndefinedRepository)
        {
            return static::allocateRepository($entity);
        }
    }

    public static function getService(string $entity) : RepositoryService
    {
        try
        {
            if(!isset(static::$services[$entity]))
                throw new UndefinedService($entity);

            return static::$services[$entity];
        }
        catch(UndefinedService)
        {
            return static::allocateService($entity);
        }
    }

    public static function &allocateRepository(string $entity) : EntityRepository
    {
        $reflection = new ReflectionClass($entity);
        $entityAttribute = $reflection->getAttributes(Entity::class);

        if (!empty($entityAttribute))
        {
            $entityAttribute = array_shift($entityAttribute);
            $entityAttribute = $entityAttribute->newInstance();
            $repositoryServiceClass = $entityAttribute->repositoryServiceClass;

            if($repositoryServiceClass::getInstance() instanceof RepositoryService)
            {
                $repository = $repositoryServiceClass::createRepository();
                return $repository;
            }
        }

        throw new FailedRepositoryAllocationException($entity);
    }

    public static function allocateService(string $entity) : RepositoryService
    {
        $reflection = new ReflectionClass($entity);
        $entityAttribute = $reflection->getAttributes(Entity::class);

        if (!empty($entityAttribute))
        {
            $entityAttribute = array_shift($entityAttribute);
            $entityAttribute = $entityAttribute->newInstance();
            $repositoryServiceClass = $entityAttribute->repositoryServiceClass;

            self::addService($repositoryServiceClass::getInstance());

            return $repositoryServiceClass::getInstance();
        }
    }

    public static function getDefaultPrimaryKeyType(): string
    {
        return self::$defaultPrimaryKeyType;
    }




    /*
    public static function executeQueryBuilderTest(EntityRepository $repository, QueryBuilder $queryBuilder) : mixed
    {
        if ($queryBuilder->isSelect())
        {
            if($repository->getCache() && $queryBuilder->isSingleSelect() && count($queryBuilder->getWhereConditions()) == 1)
            {
                $idColumn = $repository->getColumnName($repository->getPrimaryKey());
                $queryId = $queryBuilder->getWhereConditions()[$idColumn] ?? null;
                if($queryId && $repository->getCache()?->exists($queryId))
                    return $repository->getCache()->get($queryId);
            }

            $fetchMethod = ($queryBuilder->isSingleSelect()) ? 'fetchSingle' : 'fetchAll';

            $query = $queryBuilder->makeQuery();
            if($queryBuilder->getDbConnection())
            {
                $results = $queryBuilder->getDbConnection()->$fetchMethod($query, $queryBuilder->getPlaceholders());
            }
            else
            {
                $results = $repository->getConnection()->$fetchMethod($query, $queryBuilder->getPlaceholders());
            }


            if($results === false)
                return null;

            if($queryBuilder->toEntities())
            {
                return static::toEntitiesTest($repository, $results, $queryBuilder->toRelations());
            }
            else
            {
                return $results;
            }
        }
        else
        {
            return $repository->getConnection()->execute($queryBuilder->makeQuery(), $queryBuilder->getPlaceholders());
        }

    }


    public static function toEntitiesTest(EntityRepository $repository, array $data, bool $parseRelations=true)
    {
        if(empty($data)) return [];

        if(isset($data[0]))
        {
            $entities = [];
            foreach($data as $entityData)
            {
                $entity = static::createEntityTest($repository, $entityData, $parseRelations);
                $entities[$entity->getId()] = $entity;
            }

            return $entities;
        }

        return static::createEntityTest($repository, $data, $parseRelations);
    }


    public static function createEntityTest(EntityRepository $repository, array $entityData, bool $parseRelations=true)
    {
        $idField = $repository->getColumnName($repository->getPrimaryKey());
        $id = $entityData[$idField];

        if($repository->getCache()?->exists($id))
        {
            return $repository->getCache()->get($id);
        }

        $entity = $repository->createEntity();

        foreach($entityData as $column => $value)
        {
            $property = $repository->getPropertyName($column);
            $setter = "set".ucfirst($property);

            $referenceType = $repository->getReferenceTypeProperties()[$property] ?? null;

            if($referenceType)
            {
                $entity->$setter($referenceType::createFromDb($value));
            }
            else
            {
                //Its a primitive type
                $entity->$setter($value);
            }
        }

        if($repository->getCache())
        {
            $repository->getCache()->add($entity->getId(), $entity);
        }

        if($parseRelations)
        {
            static::handleEntityRelations($repository, $entity);
        }


        return $entity;
    }
    */

}
