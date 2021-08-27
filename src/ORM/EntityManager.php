<?php

namespace Polly\ORM;


use Polly\Core\Config;
use Polly\ORM\Annotations\Entity;
use Polly\ORM\Annotations\LazyMany;
use Polly\ORM\Annotations\LazyOne;
use Polly\ORM\Exceptions\FailedRepositoryAllocationException;
use Polly\ORM\Exceptions\UndefinedRepository;
use Polly\ORM\Exceptions\UnknownRelationException;
use Polly\ORM\Interfaces\INamingStrategy;
use Polly\Support\ORM\DefaultNamingStrategy;
use ReflectionClass;

class EntityManager
{
    private static ?INamingStrategy $namingStrategy = null;
    private static bool $cache = true;
    private static array $repositories = [];
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

            $results = $repository->getConnection()->$fetchMethod($queryBuilder->makeQuery(), $queryBuilder->getPlaceholders());
            if($results === false)
                return null;

            return static::toEntities($repository, $results);
        }
        else
        {
            return $repository->getConnection()->execute($queryBuilder->makeQuery(), $queryBuilder->getPlaceholders());
        }

    }

    public static function toEntities(EntityRepository $repository, array $data)
    {
        if(empty($data)) return [];

        if(isset($data[0]))
        {
            $entities = [];
            foreach($data as $entityData)
            {
                $entities[] = static::createEntity($repository, $entityData);
            }
            return $entities;
        }

        return static::createEntity($repository, $data);
    }

    public static function createEntity(EntityRepository $repository, array $entityData)
    {
        $idField = $repository->getColumnName($repository->getPrimaryKey());
        $id = $entityData[$idField];

        if($repository->getCache()?->exists($id))
        {
            return $repository->getCache()->get($id);
        }

        $entity = new ($repository->getEntity())();
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

        static::handleEntityRelations($repository, $entity);

        return $entity;
    }

    public static function handleEntityRelations(EntityRepository $repository, AbstractEntity &$entity)
    {
        foreach($repository->getRelations() as $property => $relation)
        {
            $foreignEntity        = $relation->foreignEntity;
            $foreignRepository    = static::getRepository($foreignEntity);
            $queryBuilder         = null;
            $propertySetter       = "set".ucfirst($property);

            if ($relation instanceof LazyMany)
            {
                $foreignProperty    = $relation->foreignProperty ?? lcfirst($repository->getEntityClassName()).ucfirst($repository->getPrimaryKey());
                $foreignKey         = $foreignRepository->getColumnName($foreignProperty);
                $overrideMethod     = "findBy".ucfirst($foreignProperty);

                //Check if the there is a method in the repository to retrieve the data
                if(is_callable(array($foreignRepository, $overrideMethod)))
                {
                    $entity->$propertySetter(new LazyLoader(function() use($foreignRepository, $overrideMethod, $entity) {
                        return $foreignRepository->$overrideMethod($entity->getId());
                    }));
                }
                //Else create the default QueryBuilder
                else
                {
                    $queryBuilder = (new QueryBuilder())
                        ->table($foreignRepository->getTableName())
                        ->select()
                        ->orderBy($foreignRepository->getDefaultOrderBy())
                        ->where($foreignKey, $entity->getId());

                    $entity->$propertySetter(new LazyLoader(function() use($foreignRepository, $queryBuilder) {
                        return EntityManager::executeQueryBuilder($foreignRepository, $queryBuilder);
                    }));
                }
            }
            elseif ($relation instanceof LazyOne)
            {
                $referenceProperty      = $relation->referenceProperty ?? lcfirst($foreignRepository->getEntityClassName()).ucfirst($foreignRepository->getPrimaryKey());
                $foreignProperty        = $foreignRepository->getPrimaryKey();
                $foreignKey             = $foreignRepository->getColumnName($foreignProperty);
                $getter                 = "get".ucfirst($referenceProperty);

                $queryBuilder = (new QueryBuilder())
                    ->table($foreignRepository->getTableName())
                    ->select()
                    ->single()
                    ->where($foreignKey, $entity->$getter());

                $entity->$propertySetter(new LazyLoader(function() use($foreignRepository, $queryBuilder) {
                    return EntityManager::executeQueryBuilder($foreignRepository, $queryBuilder);
                }));
            }
            else
            {
                throw new UnknownRelationException($relation::class);
            }
        }
    }

    public static function getRepository(string $entity) : EntityRepository
    {
        try
        {
            if(!isset(static::$repositories[$entity]))
                throw new UndefinedRepository($entity);

            return static::$repositories[$entity];
        }
        catch(UndefinedRepository $e)
        {
           return static::allocateRepository($entity);
        }
    }

    public static function allocateRepository(string $entity) : EntityRepository
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
                return $repositoryServiceClass::createRepository();
            }
        }

        throw new FailedRepositoryAllocationException($entity);
    }

    public static function getDefaultPrimaryKeyType(): string
    {
        return self::$defaultPrimaryKeyType;
    }



}