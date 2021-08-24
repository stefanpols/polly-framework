<?php

namespace Polly\Core;


use Exception;

class ResponseHandler
{
    private function __construct() { }

    public static function process(Response $response)
    {
        static::processHeaders($response);
        static::processHttpCode($response);
        static::processRedirect($response);

        if(!Request::expectJson())
            static::processHtmlOutput($response);
        else
            static::processJsonOutput($response);
    }

    private static function processHeaders(Response $response)
    {
        foreach($response->getHeaders() as $header)
        {
            header($header);
        }
    }

    private static function processHttpCode(Response $response)
    {
        if($response->getHttpCode())
        {
            http_response_code($response->getHttpCode());
            if(Request::isAjax())
                exit;
        }
    }

    private static function processRedirect(Response $response)
    {
        if($response->isRedirect())
        {
            $baseUrl = Router::allocateGroup()->getBaseUrl();

            $redirUrl = $baseUrl.$response->getRedirectUrl();
            if($response->addOrigin())
            {
                $redirUrl .= '?origin='.urlencode(Request::getFullUrl());
            }
            header('Location: '.$redirUrl);
            exit;
        }
    }

    private static function processHtmlOutput(Response $response)
    {
        header('Content-Type: text/html; charset=utf-8');
        if(Request::isAjax())
            $response->setViewOnly();

        echo View::render($response);
        exit;
    }

    private static function processJsonOutput(Response $response)
    {
        header("Content-type: application/json; charset=utf-8");
        $variables = $response->getVariables();
        echo json_encode($variables);
        exit;
    }

    public static function fatal(Exception $exception)
    {
        try
        {
            Logger::fatal(Logger::createFromException($exception));
        }
        catch(Exception) {}

        ob_clean();
        if(App::isDebug())
        {
            print_r($exception);
        }
        http_response_code(500);
        exit;
    }
}