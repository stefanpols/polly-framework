<?php

namespace Polly\Core;


use Closure;
use Exception;
use Polly\Exceptions\UnhandledException;
use Polly\Exceptions\UnknownExceptionHandlerException;
use Polly\Helpers\Str;
use Polly\ORM\Exceptions\InvalidEntityClassException;

class ExceptionHandler
{
    public static function process(Exception $exception)
    {
        Logger::error(Logger::createFromException($exception));

        try
        {
            //Search for the exception class in the app's config exception handlers
            if(array_key_exists($exception::class, static::getExceptionHandlers()))
                App::handleResponse(static::createResponseByHandler($exception, static::getExceptionHandlers()[$exception::class]));

            //Search for a wildcard in the app's config exception handlers
            elseif(array_key_exists('*', static::getExceptionHandlers()))
                App::handleResponse(static::createResponseByHandler($exception, static::getExceptionHandlers()['*']));

            //No valid handler found for the exception
            else
                throw new UnhandledException($exception::class);
        }
        catch(Exception $e)
        {
            App::handleFatal($e);
        }
    }

    private static function getExceptionHandlers() : array
    {
        return Config::get('exception_handlers', []);
    }

    private static function createResponseByHandler(Exception $exception, array $handler) : Response
    {
        $action = $handler['type'] ?? null;
        $target = $handler['target'] ?? null;
        $addOrigin = $handler['add_origin'] ?? false;
        $message = $handler['message'] ?? null;
        $httpCode = $handler['http_code'] ?? null;
        $headers = $handler['headers'] ?? null;

        if(!$action || empty($action))
            throw new InvalidEntityClassException($handler['type']);

        if($message && is_array($message))
        {
            if($message['title'] instanceof Closure)
                $message['title'] = $message['title']();

            if($message['description'] instanceof Closure)
                $message['description'] = $message['description']();

            Session::addMessage(new Message($message['type'], $message['title'], $message['description']));
        }

        $response = new Response();
        $response->exception = $exception;

        if($httpCode)
            $response->setHttpCode($httpCode);
        if($headers)
            $response->setHeaders($headers);

        if($action == 'view')
        {
            $response->view(Str::delete($target, '!'));

            if(Str::contains($target, '!'))
                $response->setViewOnly();
        }
        else if($action == 'redirect')
        {
            $response->redirect($target);
            $response->setAddOrigin($addOrigin);
        }
        else if($action == 'http')
        {
            //No action needed for http.
        }
        else
        {
            throw new UnknownExceptionHandlerException($exception::class.' > '.$action);
        }

        return $response;
    }

}