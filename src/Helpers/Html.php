<?php

namespace Polly\Helpers;

class Html
{
    public static function arrToAttribute(?array $objectArray)
    {
        if(empty($objectArray)) return "";
        return htmlspecialchars(json_encode($objectArray), ENT_QUOTES, 'UTF-8');
    }
}
