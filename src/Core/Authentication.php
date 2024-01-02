<?php

namespace Polly\Core;

use App\Models\User;
use Polly\Exceptions\EmptyAuthenticationHandlerException;
use Polly\Interfaces\IAuthenticationAgent;
use Polly\Interfaces\IAuthenticationModel;

class Authentication
{
    private static ?User $user = null;
    private static ?IAuthenticationAgent $authHandler = null;

    private function __construct() { }

    public static function setHandler(IAuthenticationAgent $authHandler) : void
    {
        static::$authHandler = $authHandler;
    }

    public static function login(string $username, string $password) : bool
    {
        return static::getHandler()->login($username, $password);
    }

    public static function getHandler() : IAuthenticationAgent
    {
        if(!static::$authHandler) throw new EmptyAuthenticationHandlerException();
        return static::$authHandler;
    }

    public static function logout() : bool
    {
        return static::getHandler()->logout();
    }

    public static function check() : bool
    {
        return static::getHandler()->check();
    }

    public static function user() : ?User
    {
        try {
            return static::getHandler()->user();

        } catch(EmptyAuthenticationHandlerException $e)
        {
            return self::$user;
        }
    }

    public static function setUser(?User $user) : void
    {
        try {
            static::getHandler()->setUser($user);

        } catch(EmptyAuthenticationHandlerException $e)
        {
          self::$user = $user;
        }
    }

    public static function unauthenticated()
    {
        static::getHandler()->unauthenticated();
    }
}
