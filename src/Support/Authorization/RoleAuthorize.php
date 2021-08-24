<?php

namespace Polly\Support\Authorization;

use Attribute;
use Polly\Interfaces\IAuthorizeMethod;

#[Attribute]
class RoleAuthorize implements IAuthorizeMethod
{
    public string $requiredRole;

    public function __construct(string $requiredRole)
    {
        $this->requiredRole = $requiredRole;
    }
}
