<?php

namespace Polly\Core;


class Response
{
    private array $variables = [];
    private ?string $viewPath = null;
    private ?string $module = null;
    private bool $viewOnly = false;
    private ?string $redirectUrl = null;
    private ?string $httpCode = null;
    private bool $addOrigin = false;
    private array $headers = [];
    private bool $returnJson = false;

    public function view(string $viewPath) : void
    {
        $this->viewPath = $viewPath;
    }

    public function module(string $module) : void
    {
        $this->module = $module;
    }

    public function redirect(string $redirectUrl, bool $relativeUrl = true)
    {
        $this->redirectUrl = ($relativeUrl ? Router::getCurrentBaseUrl() : '').$redirectUrl;
    }

    public function getHttpCode() : ?string
    {
        return $this->httpCode;
    }

    public function setHttpCode(?string $httpCode)
    {
        $this->httpCode = $httpCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function isViewOnly() : bool
    {
        return $this->viewOnly === true;
    }

    public function setViewOnly() : void
    {
        $this->viewOnly = true;
    }

    public function isRedirect() : bool
    {
        return !is_null($this->redirectUrl) && !empty($this->redirectUrl);
    }

    public function getRedirectUrl() : string
    {
        return $this->redirectUrl;
    }

    public function getVariables() : array
    {
        return $this->variables;
    }

    public function setArray(array $data)
    {
        $this->variables = array_values($data);
    }

    public function setObject(array $data)
    {
        $this->variables = $data;
    }

    public function getModule() : ?string
    {
        return $this->module;
    }

    public function getViewPath() : ?string
    {
        return $this->viewPath;
    }

    public function addOrigin(): bool
    {
        return $this->addOrigin;
    }

    public function setAddOrigin(bool $addOrigin): void
    {
        $this->addOrigin = $addOrigin;
    }

    public function isJson(): bool
    {
        return $this->returnJson;
    }

    public function json(): void
    {
        $this->returnJson = true;
    }

    public function &__get(string $name): mixed
    {
        return $this->variables[$name];
    }

    public function __set(string $name, mixed $value): void
    {
        $this->variables[$name] = $value;
    }
}
