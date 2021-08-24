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
}