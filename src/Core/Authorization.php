<?php

namespace Polly\Core;


use Polly\Exceptions\EmptyAuthorizationHandlerException;
use Polly\Interfaces\IAuthorizationAgent;
use Polly\Interfaces\IAuthorizeMethod;

class Authorization
{
    private static ?IAuthorizationAgent $handler = null;

    private function __construct() { }

    public static function setHandler(IAuthorizationAgent $authHandler) : void
    {
        static::$handler = $authHandler;
    }

    public static function hasAccess(IAuthorizeMethod $authorizeAttribute)
    {
        return static::getHandler()->hasAccess($authorizeAttribute);
    }

    private static function getHandler() : IAuthorizationAgent
    {
        if(!static::$handler) throw new EmptyAuthorizationHandlerException();
        return static::$handler;
    }
}


