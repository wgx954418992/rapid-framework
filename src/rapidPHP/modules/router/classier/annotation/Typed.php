<?php


namespace rapidPHP\modules\router\classier\annotation;

use rapidPHP\modules\reflection\classier\annotation\Value;
use rapidPHP\modules\router\config\AnnotationConfig;

class Typed extends Value
{

    /**
     * Typed constructor.
     * @param $value
     */
    public function __construct($value)
    {
        parent::__construct(AnnotationConfig::AT_TYPED, $value);
    }

}