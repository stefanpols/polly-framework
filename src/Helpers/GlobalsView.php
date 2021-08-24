<?php


use Polly\Core\Config;
use Polly\Core\Session;
use Polly\Core\View;
use Polly\Helpers\Arr;

function view(string $view, array $variables=[]) : string
{
    return View::include($view, $variables);
}

function asset($path = '') : string
{
    return site_url().$path.'?v='.Config::get('version', 1);
}

function datetime_to_text($format, DateTime $dateTime) : string
{
    return utf8_encode(strftime($format, $dateTime->getTimestamp()));
}

function get_messages() : mixed
{
    return Arr::objectsToArray(Session::getMessages());
}
