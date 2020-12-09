<?php

namespace rapidPHP\modules\database\sql\classier;


use Exception;
use rapidPHP\modules\core\classier\Model;
use rapidPHP\modules\reflection\classier\Classify;


class Utils
{

    /**
     * @var Utils
     */
    private static $instance;

    /**
     * 单例模式
     * @return self
     */
    public static function getInstance()
    {
        return self::$instance instanceof self ? self::$instance : self::$instance = new self();
    }

    /**
     * 格式化字段
     * @param $column
     * @param string $joinString
     * @return mixed
     */
    public function formatColumn($column, $joinString = '`')
    {
        return preg_replace('/(.*?)(\w+)/i', "$1{$joinString}$2{$joinString}", $column);
    }

    /**
     * 获取表name
     * @param $table
     * @param string $joinString
     * @return mixed
     */
    public function getTableName($table, $joinString = '`')
    {
        if (is_subclass_of($table, Model::class) && $table::NAME != null) return $this->formatColumn($table::NAME, $joinString);

        try {
            $classify = Classify::getInstance($table);

            /** @var DocComment $docComment */
            $docComment = $classify->getDocComment(DocComment::class);

            $tableAnnotation = $docComment->getTableAnnotation();

            if ($tableAnnotation) {
                $result = $tableAnnotation->getValue();

            } else {
                $result = is_object($table) ? get_class($table) : (string)$table;
            }

            return $this->formatColumn($result, $joinString);
        } catch (Exception $e) {
            $result = is_object($table) ? get_class($table) : (string)$table;

            return $this->formatColumn($result, $joinString);
        }
    }

    /**
     * 获取表字段 通过model
     * @param $model
     * @param string $joinString
     * @return mixed|string
     */
    public function getTableColumnByModel($model, $joinString = '`')
    {
        try {
            $classify = Classify::getInstance($model);

            $properties = $classify->getPropertiesNames();

            if (empty($properties)) return '*';

            return self::formatColumn(join(',', $properties), $joinString);
        } catch (Exception $e) {
            return '*';
        }

    }
}

