<?php

namespace Polly\Generator;

class TemplateBuilder
{
    private string $templatePath;
    private string $entity;

    public function __construct(string $templatePath, string $entity)
    {
        $this->templatePath = $templatePath;
        $this->entity = $entity;
    }

    public function copyTo(string $destinationPath)
    {
        $fileContent = file_get_contents($this->templatePath);
        $fileContent = str_replace("{ENTITY_NAME}", $this->entity, $fileContent);
        file_put_contents($destinationPath, $fileContent);
    }

}