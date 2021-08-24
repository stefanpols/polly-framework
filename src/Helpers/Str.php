<?php

namespace Polly\Helpers;

class Str
{
    public static function contains(string $haystack, string $needle) : bool
    {
        return str_contains($haystack, $needle);
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