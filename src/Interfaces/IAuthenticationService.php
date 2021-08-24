<?php

namespace Polly\Interfaces;

interface IAuthenticationService
{
    public static function verify(string $username, string $password) : ?IAuthenticationModel;
    public static function verifyByToken(string $token) : ?IAuthenticationModel;
    public static function createSession(IAuthenticationModel $user) : string;

}
