<?php

namespace Polly\Core;


use ErrorException;

class Shutdown
{
    private static array $catchTypes = [E_ERROR];
    private static array $logTypes = [E_ERROR];

    private function __construct() { }

    public static function handle()
    {
        $error = error_get_last();

        if($error && $error['type'])
        {
            $errorException = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);

            //Check if error should be logged
            if(in_array($error['type'], static::$logTypes) || in_array(E_ALL, static::$logTypes))
            {
                if($error['type'] == E_WARNING)
                    Logger::warning(Logger::createFromException($errorException));
                else if($error['type'] == E_NOTICE)
                    Logger::notice(Logger::createFromException($errorException));
                else if($error['type'] == E_STRICT)
                    Logger::warning(Logger::createFromException($errorException));
                else if($error['type'] == E_ERROR)
                    Logger::error(Logger::createFromException($errorException));
                else if($error['type'] == E_PARSE)
                    Logger::fatal(Logger::createFromException($errorException));
                else
                    Logger::error(Logger::createFromException($errorException));
            }

            //Check if error should be catched
            if(in_array($error['type'], static::$catchTypes) || in_array(E_ALL, static::$catchTypes))
            {
                App::handleFatal($errorException);
            }
        }
    }

    public static function prepare() : void
    {
        $shutdownConfig = Config::get('shutdown');

        if($shutdownConfig && isset($shutdownConfig['catch'])) static::$catchTypes = $shutdownConfig['catch'];
        if($shutdownConfig && isset($shutdownConfig['log'])) static::$logTypes = $shutdownConfig['log'];

        register_shutdown_function('Polly\Core\Shutdown::handle');
    }

}
