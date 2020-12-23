<?php

namespace rapidPHP\modules\core\classier\web;

use DOMDocument;
use rapidPHP\modules\core\classier\web\view\Element;

class View
{

    /**
     * 预先生成view模板
     * @param WebController $controller
     * @param $name
     * @return ViewTemplate
     */
    public static function display(WebController $controller, $name): ViewTemplate
    {
        return new ViewTemplate($controller, $name);
    }

    /**
     * @param $name
     * @param null $value
     * @param array $attr
     * @param Element|null $parent
     * @return Element
     */
    public static function createElement($name, $value = null, $attr = array(), Element $parent = null): Element
    {
        $dom = new DOMDocument('1.0');

        return (new Element($dom, $name, $value, $parent))->setAttrList($attr);
    }
}