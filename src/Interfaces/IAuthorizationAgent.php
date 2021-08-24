<?php

namespace Polly\Interfaces;

interface IAuthorizationAgent
{
    public function hasAccess(IAuthorizeMethod $authorizeAttribute) : bool;
}
