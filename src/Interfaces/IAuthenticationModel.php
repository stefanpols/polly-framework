<?php

namespace Polly\Interfaces;

interface IAuthenticationModel
{
    public function getUsername() : ?string;
    public function getPassword() : ?string;
    public function verify(string $password) : bool;

}
