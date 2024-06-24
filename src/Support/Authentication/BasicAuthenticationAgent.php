<?php

namespace Polly\Support\Authentication;

use Polly\Core\Request;
use Polly\Core\Router;
use Polly\Exceptions\AuthenticationException;
use Polly\Exceptions\InternalServerErrorException;
use Polly\Interfaces\IAuthenticationAgent;
use Polly\Interfaces\IAuthenticationModel;
use Polly\Interfaces\IAuthenticationService;

class BasicAuthenticationAgent implements IAuthenticationAgent
{
    const COOKIE_TOKEN_NAME = 'polly-auth-token';
    private static ?BasicAuthenticationAgent $instance = null;

    private ?IAuthenticationModel $user = null;
    private IAuthenticationService $userService;
    private int $expires;

    private function __construct(IAuthenticationService $userService, int $expires=0)
    {
        $this->userService = $userService;
        $this->expires = $expires > 0 ? (time() + $expires) : 0;
    }

    /**
     * BasicAuthenticationAgent singleton.
     * @param IAuthenticationService $userService
     * @param int $expires default 0 = (the cookie will expire at the end of the session
     */
    public static function getInstance(IAuthenticationService $userService, int $expires=0): IAuthenticationAgent
    {
        if(is_null(static::$instance))
            static::$instance = new BasicAuthenticationAgent($userService, $expires);

        return static::$instance;
    }

    public function check(): bool
    {
        return static::user() != null;
    }

    public function user() : ?IAuthenticationModel
    {
        if(!$this->user)
            $this->fetchUser();

        return $this->user;
    }

    private function fetchUser() : void
    {
        $token = Request::cookie(self::COOKIE_TOKEN_NAME);
        if(!empty($token))
        {
            $this->setCookie($token);
            $this->user = $this->userService::verifyByToken($token);
        }

    }

    private function setCookie(string $token) : bool
    {

        return setcookie(
            self::COOKIE_TOKEN_NAME,
            $token,
            $this->expires,
            Router::getCurrentBasePath(),
            "",
            true,
            true
        );
    }

    public function login(string $username, string $password): bool
    {
        $user = $this->userService::verify($username, $password);
        if (!$user)
            return false;


        $token = $this->userService::createSession($user);

        if (!$this->setCookie($token))
        {
            throw new InternalServerErrorException("Could not create auth token cookie");
        }

        $this->user = $user;

        return true;
    }

    public function logout(): bool
    {
        return setcookie(
            self::COOKIE_TOKEN_NAME,
            null,
            -1,
            Router::getCurrentBasePath(),
            ""
        );
    }

    public function unauthenticated()
    {
        throw new AuthenticationException();
    }
}
