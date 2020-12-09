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
     * @param string $columnRight
     * @return mixed
     */
    public function formatColumn($column, $joinString = '`')
    {
        return @preg_replace('/(\w+)/i', "{$joinString}$1{$joinString}", $column);
    }

    /**
     * 获取表name
     * @param $table
     * @return mixed
     * @throws Exception
     */
    public function getTableName($table)
    {
        if (is_subclass_of($table, Model::class) && $table::TABLE_NAME != null) return $table::TABLE_NAME;

        try {
            $classify = Classify::getInstance($table);

            /** @var DocComment $docComment */
            $docComment = $classify->getDocComment(DocComment::class);

            $tableAnnotation = $docComment->getTableAnnotation();

            return $tableAnnotation->getValue();
        } catch (Exception $e) {
            return $table;
        }
    }
    
    /**
     * 获取表字段 通过model
     * @param $model
     * @param null $column
     * @param string $joinString
     * @param string $columnRight
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

