<?php

namespace Polly\Core;

use Exception;
use Polly\Exceptions\AuthorizeException;
use Polly\Exceptions\InvalidRouteException;
use Polly\Exceptions\InvalidRouteGroupException;
use Polly\Exceptions\MissingConfigKeyException;
use Polly\Helpers\Arr;
use Polly\Helpers\Str;
use Polly\Interfaces\IAuthorizeMethod;
use Polly\ORM\Annotations\ReadOnly;
use Polly\ORM\LazyLoader;
use Polly\Support\Locale\Localized;
use ReflectionMethod;


class Router
{
    private static array $groups = [];
    private static ?string $defaultController = null;
    private static ?string $defaultMethod = null;

    private function __construct() { }

    public static function getCurrentBaseUrl()
    {
        return static::allocateGroup()->getBaseUrl();
    }

    public static function allocateGroup() : RoutingGroup
    {
        foreach(static::getGroups() as &$group)
            if(Str::contains(Request::getUrl(), $group->getBaseUrl())) return $group;

        throw new InvalidRouteGroupException(Request::getUrl());
    }

    public static function getGroups() : array
    {
        return static::$groups;
    }

    public static function getCurrentBasePath()
    {
        $basePath = static::allocateGroup()->getBaseUrl();

        $stripArray = array('http://', 'https://', Request::server('HTTP_HOST'));
        foreach($stripArray as $strip)
        {
            if(str_starts_with($basePath, $strip))
            {
                $basePath = str_replace($strip, '', $basePath);
            }
        }

        return $basePath;
    }

    public static function handleRequest() : Response
    {
        $urlFragments   = Router::getUrlFragments();
        $controller     = Router::getController($urlFragments[0]);
        $method         = Router::getAction($urlFragments[1], $controller);
        $parameters     = array_slice($urlFragments, 2);

        if(static::allocateGroup()->getAuthenticationHandler())
            Authentication::setHandler(static::allocateGroup()->getAuthenticationHandler());

        if(static::allocateGroup()->getAuthorizationAgent())
            Authorization::setHandler(static::allocateGroup()->getAuthorizationAgent());


        $response = static::call($controller, $method, $parameters);
        return $response;
    }

    public static function getUrlFragments() : array
    {
        $currentRouteGroup =  static::allocateGroup();
        $cleanUrl = Str::delete(Request::getUrl(),$currentRouteGroup->getBaseUrl());
        $urlFragments = explode('/', $cleanUrl);

        $locales        = Arr::shiftKeys(Arr::toArray(App::environment('LOCALES', ''), ['|', '=']));

        if(!empty($urlFragments) && array_key_exists($urlFragments[0], $locales))
        {
            $locale = array_shift($urlFragments);
            App::setLocale($locale);
        }

        if(empty($urlFragments) || empty($urlFragments[0]))
        {
            $urlFragments[0] = $currentRouteGroup->getDefaultController() ?: "index";
        }
        if(count($urlFragments) == 1 || strlen(trim($urlFragments[1])) == 0)
        {
            $urlFragments[1] = $currentRouteGroup->getDefaultMethod() ?: "index";
        }

        return $urlFragments;
    }

    public static function getController($controllerName)
    {
        $controllerDir = App::getBasePath().'/'.str_replace(["\\", "App"], ["/", 'app'],static::allocateGroup()->getNamespace());
        $allControllers = scandir($controllerDir);

        $translationKey = Translator::findKey($controllerName);

        if($translationKey)
        {
            foreach($allControllers as $controllerFileName)
            {
                $methodTranslations = [];
                if($controllerFileName == "." || $controllerFileName == "..")
                    continue;
                if(str_contains($controllerFileName, "Base"))
                    continue;

                $controllerClassName = str_replace(".php", "", $controllerFileName);

                $name = static::allocateGroup()->getNamespace().'\\'.$controllerClassName;
                $array = [];

                $reflectionClass = new \ReflectionClass($name);

                foreach ($reflectionClass->getAttributes() as $attribute)
                {
                    $attribute = $attribute->newInstance();

                    //Reflect properties
                    if ($attribute instanceof Localized)
                    {
                        if($translationKey == $attribute->translationKey)
                        {
                            $controllerName = $controllerClassName;
                            break 2;
                        }
                    }
                }
            }
        }

        $controllerName = Str::toCamelCase($controllerName);
        return static::allocateGroup()->getNamespace().'\\'.$controllerName;
    }

    public static function getAction($actionName,$controllerName)
    {

        $translationKey = Translator::findKey($actionName);
        if($translationKey)
        {
            $methodTranslations = [];
            $reflectionClass = new \ReflectionClass($controllerName);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                $attributes = $method->getAttributes(Localized::class);
                if($attributes)
                {
                    $methodTranslations[$attributes[0]->newInstance()->translationKey] = $method->getName();
                }
            }
            if(array_key_exists($translationKey, $methodTranslations))
            {
                return lcfirst(Str::toCamelCase($methodTranslations[$translationKey]));
            }
        }


        return lcfirst(Str::toCamelCase($actionName));
    }

    private static function throwRouteException(string $routeError)
    {
        if(!static::controllerIsPublic("*") && !Authentication::check())
            Authentication::unauthenticated();
        else
            throw new InvalidRouteException($routeError);
    }

    public static function call(string $controller, string $method, ?array $parameters) : Response
    {
        if(!class_exists($controller))
            static::throwRouteException("Call to undefined controller '".$controller."'");

        $response = new Response();
        $controllerInstance = new $controller($response);

        if(!static::canCall($controllerInstance, $method))
        {
            static::throwRouteException("Call to undefined method '".$method."' on controller '".$controller."'");
        }

        if(!static::controllerIsPublic($controller))
            if(!Authentication::check())
                Authentication::unauthenticated();

        foreach((new ReflectionMethod($controllerInstance, $method))->getAttributes() as $attribute)
            if(($attributeInstance = $attribute->newInstance()) instanceof IAuthorizeMethod && !Authorization::hasAccess($attributeInstance))
                throw new AuthorizeException($controller. " > ".$method. " | ID = ".Authentication::user()->getId());

        $controllerInstance->$method(...$parameters);

        return $response;
    }

    public static function canCall(Controller $controller, string $method) : bool
    {
        return is_callable(array($controller, $method));
    }

    public static function controllerIsPublic(string $controller) : bool
    {
        return static::allocateGroup()->checkPublic($controller);
    }

    public static function prepare() : void
    {
        
        if(!Config::exists("routing"))
            throw new MissingConfigKeyException("routing");

        $routingConfig = Config::get('routing');

        if(empty($routingConfig) || !isset($routingConfig['groups']) || empty($routingConfig['groups']))
            throw new MissingConfigKeyException("routing -> groups");

        $routingGroupConfig = $routingConfig['groups'];
        foreach($routingGroupConfig as $groupConfig)
            static::addGroup(RoutingGroup::createFromConfig($groupConfig));
    }

    public static function addGroup(RoutingGroup $group)
    {
        static::$groups[] = $group;
    }



}

