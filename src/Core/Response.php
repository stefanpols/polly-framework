<?php

namespace Polly\Core;


class Response
{
    private array $variables = [];
    private ?string $viewPath = null;
    private bool $viewOnly = false;
    private ?string $redirectUrl = null;
    private ?string $httpCode = null;
    private bool $addOrigin = false;
    private array $headers = [];

    public function view(string $viewPath) : void
    {
        $this->viewPath = $viewPath;
    }

    public function redirect(string $redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
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

    public function getViewPath() : ?string
    {
        return $this->viewPath ?? null;
    }

    public function addOrigin(): bool
    {
        return $this->addOrigin;
    }

    public function setAddOrigin(bool $addOrigin): void
    {
        $this->addOrigin = $addOrigin;
    }

    public function __get(string $name): mixed
    {
        return $this->variables[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->variables[$name] = $value;
    }
}