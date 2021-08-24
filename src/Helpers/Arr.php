<?php

namespace Polly\Helpers;

class Arr
{
    public static function objectsToArray(?array $objectArray)
    {
        $array = [];
        if(!$objectArray) return $array;
        foreach($objectArray as $object)
        {
            $array[] = json_decode(json_encode($object), true);
        }
        return $array;
    }

    public static function toArray(string $string, mixed $separators)
    {
        if(empty($separators)) return $string;
        if(!is_array($separators)) $separators = [$separators];

        $separator = array_shift($separators);
        $target = explode($separator,trim($string));

        foreach($target as $k => $item)
        {
            $target[$k] = Arr::toArray($item, $separators);
        }

        return $target;
    }

    public static function shiftKeys(array $multidimensionalArray, int $keyIndex=0)
    {
        $singleArray = [];
        foreach($multidimensionalArray as &$array)
        {
            $key = $array[$keyIndex];
            unset($array[$keyIndex]);

            $value = "";
            while(count($array) > 0)
            {
                $value .= array_shift($array);
            }

            $singleArray[$key] = $value;
        }

        return $singleArray;
    }

}