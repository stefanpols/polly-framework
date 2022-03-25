<?php


use App\Models\User;
use Polly\Core\Authentication;
use Polly\Core\Authorization;
use Polly\Core\Config;
use Polly\Core\Session;
use Polly\Core\View;
use Polly\Helpers\Arr;
use Polly\Interfaces\IAuthorizeMethod;

function user() : User
{
    return Authentication::user();
}

function has_access(IAuthorizeMethod $method) : bool
{
    return Authorization::hasAccess($method);
}

function view(string $view, array $variables=[]) : string
{
    return View::include($view, $variables);
}

function module(string $view, array $variables=[]) : string
{
    return View::module($view, $variables);
}

function asset($path = '') : string
{
    return site_url().$path.'?v='.Config::get('version', 1);
}

function datetime_to_text($format, DateTime $dateTime) : string
{
    return strftime($format, $dateTime->getTimestamp());
}

function get_messages() : array
{
    return Arr::objectsToArray(Session::getMessages());
}
