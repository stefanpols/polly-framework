<?php

namespace Polly\Interfaces;

interface IAuthenticationAgent
{
    public static function getInstance(IAuthenticationService $userService) : IAuthenticationAgent;

    public function check() : bool;

    public function user() : ?IAuthenticationModel;

    public function logout() : bool;

    public function login(string $username, string $password) : bool;

    public function unauthenticated();

}
