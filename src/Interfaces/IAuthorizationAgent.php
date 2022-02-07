<?php

namespace Polly\Interfaces;

use Polly\Support\Authorization\IRoleAuthorizationModel;
use Polly\Support\Authorization\RoleAuthorize;

interface IAuthorizationAgent
{
    public function hasAccess(IAuthorizeMethod $authorizeAttribute) : bool;
    public function getAuthorizedRoles();
    public function getRoles() : array;
    public function getUser() : IRoleAuthorizationModel;
}
