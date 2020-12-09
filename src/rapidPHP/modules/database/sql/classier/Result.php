<?php

namespace rapidPHP\modules\database\sql\classier;

use Exception;
use rapidPHP\modules\reflection\classier\Utils as ReflectionUtils;

class Result
{

    /**
     * @var string|object
     */
    private $model;

    /**
     * @var array
     */
    private $result;

    /**
     * Result constructor.
     * @param array $result
     * @param $model
     */
    public function __construct(array $result, $model)
    {
        $this->result = $result;

        $this->model = $model;
    }

    /**
     * 获取结果集
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * 获取记录里面的第几条
     * @param int $index
     * @return array
     */
    public function getOne($index = 0)
    {
        return isset($this->result[$index]) ? $this->result[$index] : [];
    }

    /**
     * 把数据转成实体对象
     * @param $model
     * @param int $index
     * @return object
     * @throws Exception
     */
    public function getInstance($model = null, $index = 0)
    {
        $model = empty($model) ? $this->model : $model;

        return ReflectionUtils::getInstance()->toObject($model, $this->getOne($index));
    }

    /**
     * 获取值
     * @param string|null $field
     * @param int $index
     * @return string|mixed|null
     */
    public function getValue(string $field, $index = 0)
    {
        $one = $this->getOne($index);

        return isset($one[$field]) ? $one[$field] : null;
    }
}