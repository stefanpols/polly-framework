<?php

namespace Polly\Core;


use Exception;

class RequestHandler
{
    private function __construct() { }

    public static function process()
    {
        try
        {
            $response = Router::handleRequest();
            App::handleResponse($response);
        }
        catch(Exception $e)
        {
            App::handleException($e);
        }
    }

}
