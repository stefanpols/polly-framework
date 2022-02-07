<?php

namespace Polly\Support\Authorization;

use Polly\Core\Authentication;
use Polly\Interfaces\IAuthorizationAgent;
use Polly\Interfaces\IAuthorizeMethod;
use RuntimeException;

class RoleAuthorizationAgent implements IAuthorizationAgent
{
    const SUPER_USER = "SUPER_USER";
    const ADMINISTRATOR = "ADMINISTRATOR";
    const DIRECTOR = "DIRECTOR";
    const MANAGER = "MANAGER";
    const EMPLOYEE = "EMPLOYEE";
    const READ_ONLY = "READ_ONLY";
    const ANONYMOUS = "ANONYMOUS";

    private static ?RoleAuthorizationAgent $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): IAuthorizationAgent
    {
        if(is_null(static::$instance))
            static::$instance = new RoleAuthorizationAgent();

        return static::$instance;
    }

    public function hasAccess(IAuthorizeMethod $authorizeAttribute) : bool
    {
        return $this->checkAccess($authorizeAttribute);
    }

    private function checkAccess(RoleAuthorize $authorize)
    {
        $requiredRole = $authorize->requiredRole;
        $currentRole = $this->getUser()->getRole();

        return array_search($currentRole, array_keys($this->getRoles())) <= array_search($requiredRole, array_keys($this->getRoles()));
    }

    public function getUser() : IRoleAuthorizationModel
    {
        if(!(Authentication::user() instanceof IRoleAuthorizationModel))
            throw new RuntimeException("'".Authentication::user()::class."' needs to include '".IRoleAuthorizationModel::class. "' if using '".RoleAuthorizationAgent::class."'");
        return Authentication::user();
    }

    /**
     * @return array Hierarchical array of roles. Highest role has most privilege
     */
    public function getRoles() : array
    {
        return [
            RoleAuthorizationAgent::SUPER_USER => "Super user",
            RoleAuthorizationAgent::ADMINISTRATOR  => "Administrator",
          //  RoleAuthorizationAgent::DIRECTOR  => "Directie",
            RoleAuthorizationAgent::MANAGER  => "Manager",
            RoleAuthorizationAgent::EMPLOYEE  => "Medewerker",
            RoleAuthorizationAgent::READ_ONLY => "Alleen-lezen",
           // RoleAuthorizationAgent::ANONYMOUS  => "Anoniem"
        ];
    }

    public function getAuthorizedRoles() : array
    {
        $roles = array_keys($this->getRoles());
        $roleKeys = (array_slice($roles, array_search(Authentication::user()->getRole(), $roles)));
        $authorizedRoles = [];
        foreach($roleKeys as $role)
            $authorizedRoles[$role] = $this->getRoles()[$role];

        return $authorizedRoles;
    }



}
