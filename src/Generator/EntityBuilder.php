<?php

namespace Polly\Generator;

use Exception;
use Polly\Helpers\Str;
use Polly\ORM\AbstractEntity;
use Polly\ORM\Annotations\Entity;
use Polly\ORM\Annotations\LazyOne;
use Polly\ORM\LazyLoader;
use ReflectionClass;

class EntityBuilder
{
    private string $className;
    private string $extends;
    private string $namespace;
    private array $methods = [];
    private array $properties = [];
    private array $annotations = [];
    private array $importNamespaces = [];

    public function __construct(string $className, $dbFields, $relations)
    {
        $this->className = $className;

        $this->namespace = "App\Models";
        $this->handleNamespace(AbstractEntity::class);
        $this->extends = "AbstractEntity";

        $this->addNamespace("App\Services\\".$this->className."Service");
        $this->addAnnotation(Entity::class, [$this->className."Service::class"]);

        $this->addDbProperties($dbFields);
        $this->addRelationProperties($relations);
    }

    public function handleNamespace(string $type)
    {
        try
        {
            $reflection = (new ReflectionClass($type));
            if(!in_array($reflection->getName(), $this->importNamespaces))
            {
                $this->addNamespace($reflection->getName());
            }

            return $reflection->getShortName();
        }
        catch(Exception $e)
        {
            //Its oke, just not a class time since reflection failed.
        }

        return $type;
    }

    public function addNamespace(string $namespace)
    {
        $this->importNamespaces[] = $namespace;
    }

    public function addAnnotation(string $name, ?array $parameters=null)
    {
        $name = $this->handleNamespace($name);
        $this->annotations[] = new Annotation($name, $parameters);
    }

    public function addDbProperties(array $properties)
    {
        foreach($properties as $name => $info)
        {
            $this->addGetSetProperty($name, $info['type']);

            if(isset($info['annotations']))
            {
                foreach($info['annotations'] as $annotation)
                {
                    $this->addPropertyAnnotation($name, $annotation);
                }
            }
        }
    }

    public function addGetSetProperty(string $name, string $type)
    {
        $property = $this->addProperty($name, $type, Property::PRIVATE, true);
        $this->createGetterAndSetter($property);
    }

    public function addProperty(string $name, string $type, string $visibility, bool $nullable) : Property
    {
        $type = $this->handleNamespace($type);
        $this->properties[$name] = $property = new Property($name, $type, $visibility, $nullable);

        return $property;
    }

    public function createGetterAndSetter(Property $property)
    {
        $this->methods[] = Method::createGetter($property);
        $this->methods[] = Method::createSetter($property);
    }

    public function addPropertyAnnotation(string $propertyName, string $annotation, ?array $parameters=null)
    {
        $variableAnnotation = $this->handleNamespace($annotation);
        $this->getProperties()[$propertyName]->getAnnotations()[] = new Annotation($variableAnnotation, $parameters);
    }

    /**
     * @return array
     */
    public function &getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     */
    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }

    public function addRelationProperties(array $properties)
    {

        foreach($properties as $name => $info)
        {

            $property = $this->addProperty($name, LazyLoader::class, Property::PRIVATE, false);
            $parameters = [];

            $parameters[] = $info['entity']."::class";
            if(array_key_exists('foreign_property', $info)) $parameters[] = "'".$info['foreign_property']."'";
            if(array_key_exists('reference_property', $info)) $parameters[] = "'".$info['reference_property']."'";

            $this->addPropertyAnnotation($name, $info['type'],$parameters);

            $method = new Method();
            $method->setName("get".ucfirst($property->getName()));
            $method->setVisibility(Method::PUBLIC);
            $method->setEntity($info['entity']);
            if($info['type'] == LazyOne::class)
            {
                $method->setReturnType('?'.$info['entity']);
            }
            else
            {
                $method->setReturnType('array');
            }

            $method->setBody("return \$this->".$property->getName().'->getResults();');
            $this->methods[] = $method;

            $method = new Method();
            $method->setName("set".ucfirst($property->getName()));
            $method->setVisibility(Method::PUBLIC);
            $method->getParameters()[] = new Parameter($property);
            $method->setBody("\$this->".$property->getName()." = $".$property->getName().';');
            $this->methods[] = $method;

        }

    }

    public function copyTo(string $destinationPath)
    {
        file_put_contents($destinationPath, $this->generate());
    }

    public function generate()
    {
        $output = "<?php"."\n"."\n";
        $output .= "namespace App\Models;"."\n"."\n";
        foreach($this->getImportNamespaces() as $namespace)
        {
            $output .= "use ".$namespace.";"."\n";
        }

        $output .= "\n";
        foreach($this->getAnnotations() as $annotation)
        {
            $output .= "#[".$annotation->getName().($annotation->getParameters() ? '('.implode(',', $annotation->getParameters()).')' : '') ."]"."\n";
        }

        $output .= "class ".$this->getClassName().(!$this->getExtends() ?: " extends ".$this->getExtends());
        $output .= "\n";
        $output .= "{";
        $output .= "\n";


        foreach($this->getProperties() as $property)
        {

            foreach ($property->getAnnotations() as $annotation)
            {
                $output .= "\t"."#[".$annotation->getName().($annotation->getParameters() ? '('.implode(',', $annotation->getParameters()).')' : '')."]";
                $output .= "\n";
            }
            $output .= "\t".$property->getVisibility().' '.($property->isNullable() ? '?' : '').$property->getType().' $'.$property->getName();
            if($property->getDefaultValue())
            {
                $output .= " = '".$property->getDefaultValue()."'";
            }
            else if($property->isNullable())
            {
                $output .= " = null";
            }
            $output .= ";";
            $output .= "\n";
            $output .= "\n";
        }

        foreach($this->getMethods() as $method)
        {
            if(str_contains($method->getBody(), "->getResults()") && $method->getReturnType() == 'array')
            {
                $output .= "\t"."/**". "\n";
                $output .= "\t"."* @return ".$method->getentity()."[] \n";
                $output .= "\t"."*/". "\n";

            }
            $output .= "\t".$method->getVisibility().' function '.$method->getName();
            $output .= '(';
            if($method->getParameters())
            {
                $parameters = $method->getParameters();
                foreach($parameters as $key => $parameter)
                {
                    $output .= ($parameter->getProperty()->isNullable() ? '?' : '').$parameter->getProperty()->getType()." $".$parameter->getProperty()->getName();
                    if($parameter->getProperty()->getDefaultValue())
                    {
                        $output .= " = '".$parameter->getProperty()->getDefaultValue()."'";
                    }
                }
                if(($key+1) < count($parameters))
                {
                    $output .= ", ";
                }
            }

            $output .= ')';
            $output .= ($method->getReturnType() ? ' : '.$method->getReturnType() : '');
            $output .= "\n";
            $output .= "\t{";
            $output .= "\n";
            $output .= "\t"."\t".$method->getBody();

            $output .= "\n";
            $output .= "\t}";
            $output .= "\n";
            $output .= "\n";
        }

        $output .= "}";

        return $output;
    }

    /**
     * @return array
     */
    public function getImportNamespaces(): array
    {
        return $this->importNamespaces;
    }

    /**
     * @param array $importNamespaces
     */
    public function setImportNamespaces(array $importNamespaces): void
    {
        $this->importNamespaces = $importNamespaces;
    }

    /**
     * @return array
     */
    public function &getAnnotations(): array
    {
        return $this->annotations;
    }

    /**
     * @param array $annotations
     */
    public function setAnnotations(array $annotations): void
    {
        $this->annotations = $annotations;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getExtends(): string
    {
        return $this->extends;
    }

    /**
     * @param string $extends
     */
    public function setExtends(string $extends): void
    {
        $this->extends = $extends;
    }

    /**
     * @return array
     */
    public function &getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param array $methods
     */
    public function setMethods(array $methods): void
    {
        $this->methods = $methods;
    }

    public function copySQL(string $destinationPath)
    {
        file_put_contents($destinationPath, $this->generateSQL());
    }

    public function generateSQL()
    {
        $output = "DROP TABLE IF EXISTS `".strtolower($this->getClassName())."`;". "\n\n";
        $output .= "CREATE TABLE `".strtolower($this->getClassName())."` ("."\n";


        $output .= "\t"."`".strtolower($this->getClassName())."_id` varchar(36) NOT NULL,". "\n";

        $properties = $this->getProperties();
        $index=0;

        $variableProperties = [];
        foreach($properties as $key => $property)
        {
            foreach($property->getAnnotations() as $annotation)
            {
                if($annotation->getName() == "Variable" || $annotation->getName() == "ForeignId")
                    $variableProperties[] = $property;
            }
        }

        $indexes = [];
        foreach($variableProperties as $key => $property)
        {
            $annotations = [];
            foreach($property->getAnnotations() as $annotation)
            {
                $annotations[] = $annotation->getName();
            }

            $columnName = Str::toSnakeCase($property->getName());
            $output .= "\t"."`".$columnName."`";

            if(in_array("ForeignId",$annotations))
            {
                $indexes[] = $columnName;
                $output .= ' varchar(36)';
            }
            else if($property->getType() == "DateTime")
            {
                $output .= ' datetime';
            }
            else if($property->getType() == "int")
            {
                $output .= ' int';
            }
            else if($property->getType() == "double")
            {
                $output .= ' double';
            }
            else if($property->getType() == "Json")
            {
                $output .= ' text';
            }
            else
            {
                $output .= ' varchar(255)';
            }

            if(in_array("NotEmpty",$annotations))
            {
                $output .= ' NOT NULL';
            }
            else
            {
                $output .= ' DEFAULT NULL';
            }

            if(($index+1) < count($variableProperties))
            {
                $output .= ", \n";
            }


            $index++;

        }

        $output .= "\n".") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $output .= "\n\n"."ALTER TABLE `".strtolower($this->getClassName())."`"."\n";
        $output .= "\t"."ADD PRIMARY KEY (`".strtolower($this->getClassName())."_id`)".($indexes ? ',' : '')."\n";
        foreach($indexes as $key => $index)
        {
            $output .= "\t"."ADD KEY `".$index."` (`".$index."`)";
            if(($key+1) < count($indexes))
            {
                $output .= ","."\n";
            }
            else
            {
                $output .= ";"."\n";
            }
        }

        return $output;


    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }




}