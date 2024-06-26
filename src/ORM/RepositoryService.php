<?php

namespace Polly\ORM;

use Polly\Core\Pagination;
use Polly\Core\Translator;
use Polly\ORM\Validation\Domain;
use Polly\ORM\Validation\Email;
use Polly\ORM\Validation\Ip;
use Polly\ORM\Validation\NotEmpty;
use Polly\ORM\Validation\Unique;
use Polly\ORM\Validation\Url;

abstract class RepositoryService
{
    abstract public static function createRepository() : EntityRepository;

    /**
     * @return static
     */
    public static function getInstance()
    {
        return new static;
    }

    /**
     * @param string $id
     * @return AbstractEntity|null
     */
    public static function findById(string $id) : ?AbstractEntity
    {
        return static::getRepository()->find($id);
    }

    abstract public static function getRepository(): EntityRepository;

    /**
     * @return AbstractEntity[]
     */
    public static function all() : array
    {
        return static::getRepository()->all();
    }

    /**
     * @param AbstractEntity $entity
     * @return bool
     */
    public static function insert(AbstractEntity $entity, bool $validate=true) : bool
    {
        if($validate && !static::validate($entity)) return false;
        if(static::getRepository()->insert($entity))
        {
            EntityManager::handleEntityRelations(static::getRepository(), $entity);
            return true;
        }
        else
        {
            echo "insert failed"."\r\n";
        }
        return false;
    }

    /**
     * @param AbstractEntity $entity
     * @return bool
     */
    public static function save(AbstractEntity $entity) : bool
    {
        if(!static::validate($entity)) return false;
        if(static::getRepository()->save($entity))
        {
            EntityManager::handleEntityRelations(static::getRepository(), $entity);
            return true;
        }
        return false;
    }

    /**
     * @param AbstractEntity $entity
     * @return bool
     */
    public static function delete(AbstractEntity $entity) : bool
    {
        if(static::getRepository()->delete($entity))
        {
            static::getRepository()?->getCache()->delete($entity->getId());
            return true;
        }
        return false;
    }

    public static function getPage(int $page, $results=100) : Pagination
    {
        $pagination = new Pagination();
        $pagination->setCurrentPage($page);
        $pagination->setResultsPerPage($results);
        $pagination->setResults(static::getRepository()?->limited($pagination->getResultsPerPage(), $pagination->getOffset()));
        return $pagination;
    }

    public static function validate(AbstractEntity $entity) : bool
    {
        $errors = [];

        foreach(static::getRepository()->getValidators() ?? [] as $property => $validators)
        {
            $getter  = 'get'.ucfirst($property);
            $value   = $entity->$getter();

            foreach($validators as $validator)
            {
                if($validator instanceof NotEmpty)
                {
                    if(is_null($value))
                    {
                        $errors[$property] = Translator::translate('not_empty_validation_error');
                    }
                    else if(is_string($value) && strlen(trim($value)) == 0)
                    {
                        $errors[$property] = Translator::translate('not_empty_validation_error');
                    }
                }
                elseif($validator instanceof Email && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL))
                {
                    $errors[$property] = Translator::translate('email_validation_error');
                }
                elseif($validator instanceof Url && !empty($value) && !filter_var($value, FILTER_VALIDATE_URL))
                {
                    $errors[$property] = Translator::translate('url_validation_error');
                }
                elseif($validator instanceof Ip && !empty($value) && !filter_var($value, FILTER_VALIDATE_IP))
                {
                    $errors[$property] = Translator::translate('ip_validation_error');
                }
                elseif($validator instanceof Domain && !empty($value) && !filter_var($value, FILTER_VALIDATE_DOMAIN))
                {
                    $errors[$property] = Translator::translate('domain_validation_error');
                }
                elseif($validator instanceof Unique && !empty($value))
                {
                    $existingItems = static::getRepository()->allWhere($property, $value);
                    if($existingItems)
                        foreach($existingItems as $existingItem)
                            if($existingItem->getId() != $entity->getId())
                                $errors[$property] = Translator::translate('unique_validation_error');
                }
            }
        }

        $entity->setErrors($errors);
        return empty($errors);

    }



}
