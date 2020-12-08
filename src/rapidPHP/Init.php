<?php


namespace rapidPHP;

use Exception;
use rapidPHP\modules\common\classier\AB;
use rapidPHP\modules\common\classier\AR;
use rapidPHP\modules\common\classier\Build;
use rapidPHP\modules\common\classier\Calendar;
use rapidPHP\modules\common\classier\Console;
use rapidPHP\modules\common\classier\File;
use rapidPHP\modules\common\classier\Register;
use rapidPHP\modules\common\classier\StrCharacter;
use rapidPHP\modules\common\classier\Verify;
use rapidPHP\modules\common\classier\Xml;
use rapidPHP\modules\core\classier\web\ViewTemplate;
use rapidPHP\modules\reflection\classier\Classify;

// 检测PHP环境
if (version_compare(PHP_VERSION, '7.1.0', '<')) die('require PHP > 7.1.0 !');

//运行模式
define('RAPIDPHP_VERSION', '3.1.1');

//运行模式
define('APP_RUNNING_SAPI_NAME', php_sapi_name());

//运行模式是否命令运行
define('APP_RUNNING_IS_SHELL', isset($_SERVER['SHELL']));

//项目根目录
define('PATH_ROOT', str_replace('\\', '/', dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . '/');

//rapidPHP 框架根目录
define('PATH_RAPIDPHP_ROOT', str_replace('\\', '/', dirname(dirname(__DIR__))) . '/src/');

//当前框架根目录
define('PATH_FRAMEWORK', PATH_RAPIDPHP_ROOT . 'rapidPHP/');

//modules目录
define('PATH_MODULES', PATH_FRAMEWORK . 'modules/');

//当前运行文件根目录
define('PATH_RUNTIME', PATH_ROOT . 'runtime/');

//当前app运行文件目录
define('PATH_APP_RUNTIME', PATH_APP . 'runtime/');

/**
 * 快捷获取ArrayObject类
 * @param $array
 * @return AB
 */
function AB($array = null)
{
    return AB::getInstance($array);
}

/**
 * 快捷获取Array类
 * @return AR
 */
function AR()
{
    return AR::getInstance();
}

/**
 * 快捷获取build类
 * @return Build
 */
function B()
{
    return Build::getInstance();
}


/**
 * 快捷获取Console类
 * @return Console
 */
function Con()
{
    return Console::getInstance();
}

/**
 * 快捷获取StrCharacter类
 * @return StrCharacter
 */
function Str()
{
    return StrCharacter::getInstance();
}

/**
 * 快捷获取Calendar类
 * @return Calendar
 */
function Cal()
{
    return Calendar::getInstance();
}

/**
 * 获取获得文件操作类
 * @return File
 */
function F()
{
    return File::getInstance();
}

/**
 * 获取获得验证操作类
 * @return Verify
 */
function V()
{
    return Verify::getInstance();
}

/**
 * 获取获得xml操作类
 * @return Xml
 */
function X()
{
    return Xml::getInstance();
}

/**
 * 实例化类中转站，如果类已经实例化过则自动取出之前的
 * @param $name string 类名字，如果用命名空间则需要带上命名空间
 * @param array $parameter
 * @param bool $forced
 * @return array|null|object|string
 * @throws Exception
 */
function M($name, $parameter = [], $forced = false)
{
    if ($forced == false) {
        if (Register::getInstance()->isPut($name)) {
            return Register::getInstance()->get($name);
        } else {
            $object = Classify::getInstance($name)->newInstanceArgs($parameter);

            Register::getInstance()->put($name, $object);

            return $object;
        }
    } else {
        $object = Classify::getInstance($name)->newInstanceArgs($parameter);

        Register::getInstance()->put($name, $object);

        return $object;
    }
}

/**
 * 获取当前view的 array object
 * @param $view
 * @return ViewTemplate
 */
function VT($view)
{
    if ($view instanceof ViewTemplate) return $view;

    return null;
}

/**
 * 格式化异常
 * format Exception
 * @param Exception $e
 * @param string $format
 * @return string
 */
function formatException(Exception $e, $format = "{msg} {code}\n{trace}\n thrown in {file} on line {line}")
{
    $result = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ];

    foreach ($result as $key => $value) {
        $format = str_replace("{{$key}}", $value, $format);
    }

    return $format;
}

class Init
{
    /**
     * 用于composer 依赖加载
     */
    public static function load()
    {
    }
}