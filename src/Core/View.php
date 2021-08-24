<?php

namespace Polly\Core;

use Polly\Exceptions\MissingConfigKeyException;
use Polly\Exceptions\ViewNotFoundException;
use Polly\Helpers\FileSystem;

class View
{
    private function __construct() { }

    public static function render(Response $response)
    {
        if(!$response->getViewPath())
        {
            return;
        }
        $viewOutput = static::include($response->getViewPath(), $response->getVariables());
        if(!$response->isViewOnly())
        {
            $viewOutput = static::include(static::getBase(), ['content'=>$viewOutput]);
        }

        return $viewOutput;
    }

    public static function include(string $view, array $variables)
    {
        require_once __DIR__ . '/../Helpers/GlobalsView.php';

        $viewPath = static::getPath().'/'.$view.'.php';

        if(!FileSystem::fileExists($viewPath))
            throw new ViewNotFoundException($viewPath);

        ob_start();
        extract($variables);
        include $viewPath;
        $content = ob_get_clean();

        if(Config::get("compress_output", false))
        {
            $content = static::sanitize($content);
        }
        return $content;
    }

    public static function getPath()
    {
        if(!Config::exists('path.views'))
            throw new MissingConfigKeyException('path.views');

        return Config::get('path.views');
    }

    public static function sanitize($buffer) {

        $search = array(
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/' // Remove HTML comments
        );

        $replace = array(
            '>',
            '<',
            '\\1',
            ''
        );

        $buffer = preg_replace($search, $replace, $buffer);

        return $buffer;
    }

    public static function getBase()
    {
        if(!Config::exists('path.views.base'))
            throw new MissingConfigKeyException('path.views.base');

        return Config::get('path.views.base');
    }

}