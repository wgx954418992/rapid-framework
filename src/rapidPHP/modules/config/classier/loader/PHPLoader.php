<?php


namespace rapidPHP\modules\config\classier\loader;


use Exception;
use rapidPHP\modules\common\classier\Instances;
use rapidPHP\modules\common\classier\Path;
use rapidPHP\modules\config\classier\ILoader;
use function rapidPHP\B;

class PHPLoader implements ILoader
{


    /**
     * 单例模式
     */
    use Instances;

    /**
     * 初始化当前
     * @return static
     */
    public static function onNotInstance()
    {
        return new static();
    }


    /**
     * 是否支持
     * @param string $filename
     * @return bool
     */
    public function isSupport(string $filename): bool
    {
        $info = Path::getInstance()->getPathInfo($filename);

        $ext = B()->getData($info, 'suffix');

        return strtolower($ext) === 'php';
    }

    /**
     * load php
     * @param string $filename
     * @return array
     * @throws Exception
     */
    public function load(string $filename): array
    {
        if (!is_file($filename)) throw new Exception('文件错误!');

        return include($filename . '');
    }
}
