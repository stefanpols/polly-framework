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

        if($createIfNotExists)
        {
            mkdir($directoryPath, 0777,true);
            return is_dir($directoryPath);
        }
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

    public static function getDirectoryContent($directoryPath, $ignoreDirs=array(), $ignoreFiles=array(), &$results = array())
    {
        $files = scandir($directoryPath);

        foreach ($files as $value)
        {
            $path = realpath($directoryPath .'/'. $value);
            $path = str_replace('\\','/', $path);

            if (!is_dir($path))
            {
                foreach($ignoreFiles as $ignoreFile)
                    if(str_ends_with($value, $ignoreFile))
                        continue 2;

                $results[] = $path;
            }
            else if ($value != "." && $value != "..")
            {
                foreach($ignoreDirs as $ignoreDir)
                    if(str_ends_with($value, $ignoreDir))
                        continue 2;

                $results[] = $path;
                FileSystem::getDirectoryContent($path, $ignoreDirs, $ignoreFiles, $results);
            }
        }

        return $results;
    }

    public static function removeDirectory($directoryPath)
    {
        if (is_dir($directoryPath))
        {
            $objects = scandir($directoryPath);
            foreach ($objects as $object)
            {
                if ($object != "." && $object != "..")
                {
                    if (is_dir($directoryPath.'/'.$object) && !is_link($directoryPath."/".$object))
                        static::removeDirectory($directoryPath.'/'.$object);
                    else
                        unlink($directoryPath.'/'.$object);
                }
            }
            rmdir($directoryPath);
        }
    }

}
