<?php

namespace Polly\Core;


use Polly\Interfaces\IAuthenticationAgent;
use Polly\Interfaces\IAuthorizationAgent;

class RoutingGroup
{
    private string $baseUrl;
    private string $namespace;
    private array $public;
    private ?IAuthenticationAgent $authentication;
    private ?IAuthorizationAgent $authorization;

    public static function createFromConfig(array $groupConfig) : RoutingGroup
    {
        $routingGroup                   = new RoutingGroup();
        $routingGroup->baseUrl          = $groupConfig['base_url'] ?? '*';
        $routingGroup->namespace        = $groupConfig['namespace'] ?? '';
        $routingGroup->public           = $groupConfig['public'] ?? [];
        $routingGroup->authentication   = $groupConfig['authentication'] ?? null;
        $routingGroup->authorization    = $groupConfig['authorization'] ?? null;
        return $routingGroup;
    }

    public function checkPublic(string $controllerClass) : bool
    {
        return in_array('*', $this->getPublic()) || in_array($controllerClass, $this->getPublic());
    }

    /**
     * @return array
     */
    public function getPublic(): array
    {
        return $this->public;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return rtrim($this->baseUrl,"/").'/';
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return IAuthenticationAgent|null
     */
    public function getAuthenticationHandler() : ?IAuthenticationAgent
    {
        return $this->authentication;
    }

    public function getAuthorizationAgent() : ?IAuthorizationAgent
    {
        return $this->authorization;
    }

}