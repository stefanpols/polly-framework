<?php

namespace Polly\Core;

use ArrayObject;
use Polly\Exceptions\MissingConfigKeyException;
use Polly\Exceptions\ViewNotFoundException;
use Polly\Helpers\FileSystem;
use Polly\Helpers\Str;

class View
{
    private static ?ArrayObject $cssPaths = null;
    private static ?ArrayObject $jsPaths = null;

    public static function &getCssPaths(): ArrayObject
    {
        if(!static::$cssPaths)
            static::$cssPaths= new ArrayObject();
        return self::$cssPaths;
    }

    public static function &getJsPaths(): ArrayObject
    {
        if(!static::$jsPaths)
            static::$jsPaths= new ArrayObject();
        return self::$jsPaths;
    }

    private function __construct() { }

    public static function render(Response $response) : string
    {
        if(!$response->getViewPath() && !$response->getModule())
        {
            return "";
        }

        if($response->getViewPath())
        {
            $viewOutput = static::include($response->getViewPath(), $response->getVariables());
        }
        else
        {
            $viewOutput = static::module($response->getModule(), $response->getVariables());
        }

        if(!$response->isViewOnly())
        {
            $viewOutput = static::include(static::getBase(), ['content'=>$viewOutput, 'cssPaths'=>static::getCssPaths(), 'jsPaths'=>static::getJsPaths()]);
        }

        return $viewOutput;
    }

    public static function module(string $module, array $variables)
    {
        $moduleArray    = explode('/', $module);
        $moduleName     = array_pop($moduleArray);
        $fileName       = Str::toKebabCase($moduleName);
        $viewPath       = $module.'/View';

        if(is_file(static::getPath().'/'.$module.'/_script.js'))
        {
            $jsPath = 'modules/'.Str::toKebabCase($module).'/_script.js';
            static::getJsPaths()[$jsPath]   = $jsPath;
        }
        if(is_file(static::getPath().'/'.$module.'/_style.css'))
        {
            $cssPath = 'modules/'.Str::toKebabCase($module).'/_style.css';
            static::getCssPaths()[$cssPath] = $cssPath;
        }

        return static::include($viewPath, array_merge($variables,['cssPaths'=>static::getCssPaths(), 'jsPaths'=>static::getJsPaths()]));
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
