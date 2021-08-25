<?php

namespace Polly\Core;

use DateTime;
use Exception;
use Polly\Helpers\FileSystem;


class Logger
{
    private static ?string $logFilePath = null;

    private function __construct() { }

    public static function fatal(string $log) : void { static::writeLog("FATAL", $log); }

    private static function writeLog(string $type, string $log) : void
    {
        $log =  trim(preg_replace('/\s+/', ' ', $log));

        if(static::$logFilePath)
        {
            error_log(
                '[' . (new DateTime())->format('d-m-Y H:i:s') . "] [".$type."] ".$log."\n",
                3,
                static::$logFilePath);
        }
    }

    public static function error(string $log) : void { static::writeLog("ERROR", $log); }

    public static function warning(string $log) : void { static::writeLog("WARNING", $log); }

    public static function notice(string $log) : void { static::writeLog("NOTICE", $log); }

    public static function debug(string $log) : void { static::writeLog("DEBUG", $log); }

    public static function createFromException(Exception $exception) : string
    {
        $log  = "[FILE]: ".$exception->getFile(). " (line:" . $exception->getLine() . ")"." ";
        $log .= "[EXCEPTION]: ".$exception::class." ";
        $log .= "[URL]: ".($_SERVER['REQUEST_URI'] ?? "CLI")." ";
        if($exception->getMessage())
            $log .= "[MESSAGE]: ".$exception->getMessage()." ";
        $log .= "[TRACE]: ".str_replace("\n", ' ', $exception->getTraceAsString());

        return $log;
    }

    public static function prepare(): void
    {
        $logConfig = Config::get('log');
        if(!$logConfig || !isset($logConfig['file_path'])) return;

        $logFilePath = $logConfig['file_path'];
        if($logFilePath && FileSystem::createPath($logFilePath))
        {
            static::$logFilePath = $logFilePath;
        }
    }
}