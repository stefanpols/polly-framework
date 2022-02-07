<?php

namespace Polly\Core;

use JsonSerializable;

class Message implements JsonSerializable
{
    public const SUCCESS = 'success';
    public const INFO = 'info';
    public const WARNING = 'warning';
    public const DANGER = 'danger';

    private string $type;
    private string $title;
    private string $description;

    public function __construct(string $type, string $title, string $description)
    {
        if( $type != Message::SUCCESS &&
            $type != Message::INFO &&
            $type != Message::WARNING &&
            $type != Message::DANGER)
        {
            $type = Message::INFO;
        }

        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
    }

    public function jsonSerialize()
    {
        return ['type'=>$this->type, 'title'=>$this->title, 'description'=>$this->description];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }


}
