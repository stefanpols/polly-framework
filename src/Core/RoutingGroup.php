<?php

namespace Polly\Core;


use Polly\Interfaces\IAuthenticationAgent;
use Polly\Interfaces\IAuthorizationAgent;

class RoutingGroup
{
    private string $baseUrl;
    private string $namespace;
    private string $viewDir;
    private string $defaultController;
    private string $defaultMethod;
    private array $public;
    private array $exceptionHandlers;
    private ?IAuthenticationAgent $authentication;
    private ?IAuthorizationAgent $authorization;

    public static function createFromConfig(array $groupConfig) : RoutingGroup
    {
        $routingGroup                       = new RoutingGroup();
        $routingGroup->baseUrl              = $groupConfig['base_url'] ?? '*';
        $routingGroup->namespace            = $groupConfig['namespace'] ?? '';
        $routingGroup->public               = $groupConfig['public'] ?? [];
        $routingGroup->authentication       = $groupConfig['authentication'] ?? null;
        $routingGroup->authorization        = $groupConfig['authorization'] ?? null;
        $routingGroup->exceptionHandlers    = $groupConfig['exception_handlers'] ?? [];
        $routingGroup->viewDir              = $groupConfig['view_dir'] ?? "";
        $routingGroup->defaultController    = $groupConfig['default_controller'] ?? "";
        $routingGroup->defaultMethod        = $groupConfig['default_method'] ?? "";
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

    /**
     * @return array
     */
    public function getExceptionHandlers() : array
    {
        return $this->exceptionHandlers;
    }

    /**
     * @return string
     */
    public function getViewDir(): string
    {
        return $this->viewDir;
    }

    /**
     * @param string $viewDir
     */
    public function setViewDir(string $viewDir): void
    {
        $this->viewDir = $viewDir;
    }

    /**
     * @return string
     */
    public function getDefaultController(): string
    {
        return $this->defaultController;
    }

    /**
     * @param string $defaultController
     */
    public function setDefaultController(string $defaultController): void
    {
        $this->defaultController = $defaultController;
    }

    /**
     * @return string
     */
    public function getDefaultMethod(): string
    {
        return $this->defaultMethod;
    }

    /**
     * @param string $defaultMethod
     */
    public function setDefaultMethod(string $defaultMethod): void
    {
        $this->defaultMethod = $defaultMethod;
    }




}
