<?php

namespace Polly\Helpers;

class Str
{
    public static function contains(string $haystack, string $needle) : bool
    {
        return str_contains($haystack, $needle);
    }

    public static function hasSimilarity(string $string1, string $string2) : bool
    {
        return str_contains($string1, $string2) || str_contains($string2, $string1);
    }

    public static function toFileName($file)
    {
        $file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file);
        $file = mb_ereg_replace("([\.]{2,})", '', $file);
        return $file;
    }

    public static function toCleanString($string, $spaceDivider='')
    {
        if($string == null) return "";
        $string = str_replace(' ', $spaceDivider, $string);
        $string = iconv('utf-8', 'utf-8//IGNORE', $string);

        return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $string), '-'));
    }

    public static function delete(string $subject, string $search) : string
    {
        return str_replace($search, "", $subject);
    }

    public static function toCamelCase($string)
    {
        $string = str_replace(["  ", "","-",'_'], ' ', $string);
        $stringParts = explode(' ',$string);
        return implode("", array_map(function($part){ return ucwords($part); }, $stringParts));
    }

    public static function toSnakeCase(string $string)
    {
        return strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", $string));
    }

    public static function toKebabCase(string $string)
    {
        return strtolower(preg_replace("/([a-z])([A-Z])/", "$1-$2", $string));
    }

    public static function random($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++)
        {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
