<?php

namespace Polly\Core;

class Session
{
    private function __construct() { }

    public static function addMessage(Message $message)
    {
        if(static::get('polly_messages') == null)
        {
            static::set('polly_messages', []);
        }
        static::add('polly_messages', $message);
    }

    public static function get(string $name): mixed
    {
        static::start();
        return $_SESSION[$name] ?? null;
    }

    public static function start()
    {
        if(session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE)
            session_start();
    }

    public static function set(string $name, mixed $value): void
    {
        static::start();
        if($value == null && static::get($name) !== null)
        {
            unset($_SESSION[$name]);
        }
        else
        {
            $_SESSION[$name] = $value;
        }
    }

    public static function add(string $name, mixed $value) : void
    {
        if(Session::get($name) == null)
        {
            Session::set($name, []);
        }

        $_SESSION[$name][] = $value;
    }

    public static function getMessages() : mixed
    {
        $messages = static::get('polly_messages');
        static::set('polly_messages', null);
        return $messages;
    }

    public static function destroy()
    {
        static::start();
        session_destroy();
    }

    public static function close()
    {
        static::start();
        session_write_close();
    }

}