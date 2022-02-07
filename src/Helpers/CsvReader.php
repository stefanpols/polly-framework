<?php

namespace Polly\Helpers;

class CsvReader
{
    private static $delimiter;
    public static function fileToArray($filePath, $delimiter=";")
    {
        try
        {
            self::$delimiter = $delimiter;
            $csv = array_map(function($v){return str_getcsv($v, self::$delimiter);}, file($filePath));
            
            array_walk($csv, function(&$a) use ($csv) 
            {
                $a = @array_combine($csv[0], $a);
            });  
        }
        catch(\Exception $e)
        {
            return array();
        }
        return $csv;
    }
}
