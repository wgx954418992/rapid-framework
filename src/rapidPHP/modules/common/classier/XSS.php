<?php

namespace rapidPHP\modules\common\classier;

use voku\helper\AntiXSS;

class XSS
{

    /**
     * @var AntiXSS
     */
    protected static $antiXss;

    /**
     * 采用单例模式
     */
    use Instances;

    /**
     * 实例不存在
     * @return static
     */
    public static function onNotInstance()
    {
        self::$antiXss = new AntiXSS();

        return new static();
    }

    /**
     * 过滤
     * @param mixed|null $data
     */
    public function filter(&$data)
    {
        if (is_array($data)) {
            foreach ($data as &$value) {
                $this->filter($value);
            }
        } else if (is_object($data)) {
            foreach ($data as &$value) {
                $this->filter($value);
            }
        } else {
            $data = self::$antiXss->xss_clean($data);
        }
    }
}