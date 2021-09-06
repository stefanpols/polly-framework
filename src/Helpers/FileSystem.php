<?php

namespace Polly\Helpers;

class FileSystem
{

    public static function createPath(string $filePath) : bool
    {
        if(is_file($filePath)) return true;

        if(static::directoryExists(dirname($filePath), true))
        {
            return static::fileExists($filePath, true);
        }
        return false;
    }

    public static function directoryExists(string $directoryPath, bool $createIfNotExists=false) : bool
    {
        if(is_dir($directoryPath)) return true;

        if(!is_dir($directoryPath))
        {
            mkdir($directoryPath, 0777,true);
        }

        return is_dir($directoryPath);
    }

    public static function fileExists(?string $filePath, bool $createIfNotExists=false) : bool
    {
        if(is_null($filePath)) return false;
        if(is_file($filePath)) return true;

        if($createIfNotExists)
        {
            $file = fopen($filePath, 'w');
            if($file)
            {
                fclose($file);
                return true;
            }
        }
        return false;
    }

    public static function getDirectoryContent($directoryPath, &$results = array())
    {
        $files = scandir($directoryPath);

        foreach ($files as $value)
        {
            $path = realpath($directoryPath . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path))
            {
                $results[] = $path;
            }
            else if ($value != "." && $value != "..")
            {
                FileSystem::getDirectoryContent($path, $results);
                $results[] = $path;
            }
        }

        return $results;
    }

}
