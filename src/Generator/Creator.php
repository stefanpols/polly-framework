<?php

namespace Polly\Generator;

use Polly\Helpers\FileSystem;
use Polly\Helpers\Str;

class Creator
{

    public static function all(string $entity, array $dbFields, array $relations, string $templatePath, array $directories=null)
    {
        (new TemplateBuilder($templatePath."/repository.gen", $entity))->copyTo($directories['repository'].'/'.$entity.'Repository.php');
        (new TemplateBuilder($templatePath."/service.gen", $entity))->copyTo($directories['service'].'/'.$entity.'Service.php');
        (new TemplateBuilder($templatePath."/controller.gen", $entity))->copyTo($directories['controller'].'/'.$entity.'.php');
        FileSystem::createPath($directories['view'].'/'.$entity.'/Index.php');
        $entityGenerator = new EntityBuilder($entity, $dbFields, $relations);
        $entityGenerator->copyTo($directories['model'].'/'.$entity.'.php');
        FileSystem::createPath($templatePath.'/../generated/'.Str::toSnakeCase($entity).'.sql');
        $entityGenerator->copySQL($templatePath.'/../generated/'.Str::toSnakeCase($entity).'.sql');

        return [
            $directories['repository'].'/'.$entity.'Repository.php',
            $directories['service'].'/'.$entity.'Service.php',
            $directories['controller'].'/'.$entity.'.php',
            $directories['view'].'/'.$entity.'/Index.php',
            $directories['model'].'/'.$entity.'.php',
            $templatePath.'/../generated/'.strtolower($entity).'.sql'
        ];

    }

}
