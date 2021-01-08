<?php

namespace rapidPHP\modules\console\classier;

use rapidPHP\modules\common\classier\XSS;
use rapidPHP\modules\io\classier\Input as IOInput;

class Input implements IOInput
{

    /**
     * @var Args
     */
    private $args;

    /**
     * Input constructor.
     * @param Args $args
     */
    public function __construct(Args $args)
    {
        $this->args = $args;
    }

    /**
     * 获取参数
     * @param null $name
     * @return array|int|mixed|object|string|null
     */
    public function get($name = null)
    {
        if (is_null($name)) return $this->args;

        $value = $this->args->getOptionValue($name);

        XSS::getInstance()->filter($value);

        return $value;
    }

    /**
     * @return Args
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * 获取选项值
     * @param $name
     * @return array|string|NULL
     */
    public function getOptionValue($name)
    {
        $value = $this->args->getOptionValue($name);

        XSS::getInstance()->filter($value);

        return $value;
    }

    /**
     * 获取命令行参数值
     * @param $name
     * @return array|string|NULL
     */
    public function getArgsValue($name)
    {
        $value = $this->args->getArgsValue($name);

        XSS::getInstance()->filter($value);

        return $value;
    }
}