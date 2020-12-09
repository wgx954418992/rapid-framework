<?php

namespace rapidPHP\modules\database\sql\classier;

use Exception;
use PDOException;
use rapidPHP\modules\common\classier\StrCharacter;
use rapidPHP\modules\database\sql\config\SqlConfig;

abstract class Driver
{

    /**
     * 连接对象
     * @var SQLDB
     */
    private $db;

    /**
     * 表名
     * @var mixed|null
     */
    protected $tableName = null;

    /**
     * 字段
     * @var mixed|null
     */
    protected $tableColumn = null;

    /**
     * 预先执行参数
     * @var array
     */
    protected $options = [];

    /**
     * sql语句
     * ->defaultSql
     * @var array
     */
    protected $sql;

    /**
     * `id`
     * @var string
     */
    protected $joinString = '';

    /**
     * Driver constructor.
     * @param SQLDB $db
     * @param null $modelOrClass
     * @throws Exception
     */
    public function __construct(SQLDB $db, $modelOrClass = null)
    {
        $this->db = $db;

        $this->sql = SqlConfig::SQL_LIST;

        $this->tableName = Utils::getInstance()->getTableName($modelOrClass, $this->joinString);

        $this->tableColumn = Utils::getInstance()->getTableColumnByModel($modelOrClass, $this->joinString);
    }

    /**
     * 重置sql语句
     * @param null $name
     * @return $this
     */
    public function resetSql($name = null)
    {
        if (is_null($name)) {
            $this->sql = SqlConfig::SQL_LIST;
        } else if (isset($this->sql[$name])) {
            $this->sql[$name] = null;
        }
        return $this;
    }

    /**
     * 获取别的driver sql
     * @param $callOrDriver
     * @param bool $isMergeOptions
     * @return $this|string|string[]
     */
    private function getDriverSql($callOrDriver, $isMergeOptions = true)
    {
        if (is_callable($callOrDriver)) {
            $driver = call_user_func($callOrDriver, $this);
        } else {
            $driver = $callOrDriver;
        }

        if (!($driver instanceof Driver)) return null;

        $sql = $driver->getSql();

        $options = $driver->getOptions();

        foreach ($options as $name => $value) {
            $key = $this->getOptionsKey($name);

            $name = ":{$name}";

            $position = strpos($sql, $name);

            if (is_int($position)) {
                $sql = substr_replace($sql, ':' . $key, $position, strlen($name));

                if ($isMergeOptions) $this->addOptions($value, $key);
            }
        }

        return $sql;
    }

    /**
     * 创建数据库
     * @param $dataBaseName
     * @return $this
     */
    public function createDataBase($dataBaseName)
    {
        $this->sql['query'] = "CREATE DATABASE " . Utils::getInstance()->formatColumn($dataBaseName, $this->joinString) . ' ';

        return $this;
    }

    /**
     * 创建表单
     * @param array $column
     * @return $this
     */
    public function createTable(array $column = [])
    {
        $values = '';

        $column = $column ? $column : isset($this->tableColumn) ? $this->tableColumn : [];

        foreach ($column as $name => $value) {
            $name = Utils::getInstance()->formatColumn($name, $this->joinString);

            $values .= "{$name} {$value} ,";
        }

        $values = StrCharacter::getInstance()->deleteStringLast($values);

        $this->sql['query'] = "CREATE TABLE {$this->tableName} ({$values}) ";

        return $this;
    }


    /**
     * 执行存储过程
     * @param array $parameter
     * @param string $value
     * @return $this
     */
    public function func($parameter = [], $value = '')
    {
        return $this;
    }


    /**
     * 写到数据
     * @param $data
     * @return $this
     */
    public function insert($data)
    {
        $data = $this->makeInsertData($data);

        $this->sql['insert'] = "INSERT INTO {$this->tableName} ({$data['keys']}) VALUES ({$data['values']}) ";

        return $this;
    }


    /**
     * 生成写入数据
     * @param $data
     * @return array
     */
    private function makeInsertData($data)
    {
        $array = ['keys' => '', 'values' => ''];

        foreach ($data as $item => $value) {
            if (is_null($value)) continue;

            $array['keys'] .= Utils::getInstance()->formatColumn($item, $this->joinString) . ",";

            if (substr($value, 0, 2) !== ':$') {
                $optionsKey = $this->getOptionsKey($item);

                $array['values'] .= ":{$optionsKey},";

                $this->addOptions($value, $optionsKey);
            } else {
                $array['values'] .= substr($value, 2) . ",";
            }
        }

        $array['keys'] = StrCharacter::getInstance()->deleteStringLast($array['keys']);

        $array['values'] = StrCharacter::getInstance()->deleteStringLast($array['values']);

        return $array;
    }


    /**
     * 修改
     * @param array $data
     * @return $this
     */
    public function update(array $data)
    {
        $this->sql['update'] = "UPDATE {$this->tableName} SET " . $this->makeUpdateData($data);

        return $this;
    }

    /**
     * 生成update数据
     * @param array $data
     * @return string
     */
    private function makeUpdateData(array $data)
    {
        $setting = '';

        foreach ($data as $name => $value) {
            if (is_callable($value) || $value instanceof Driver) {
                $name = Utils::getInstance()->formatColumn($name, $this->joinString);

                $setting .= "{$name}=({$this->getDriverSql($value)}),";
            } else {
                $optionsKey = $this->getOptionsKey($name);

                $name = Utils::getInstance()->formatColumn($name, $this->joinString);

                if (substr($value, 0, 2) !== ':$') {

                    $setting .= "{$name}=:{$optionsKey},";

                    $this->addOptions($value, $optionsKey);
                } else {
                    $setting .= "{$name}=" . substr($value, 2) . ',';
                }
            }
        }

        return StrCharacter::getInstance()->deleteStringLast($setting);
    }


    /**
     * 删除
     * @param null $callOrDriver
     * @return $this
     */
    public function delete($callOrDriver = null)
    {
        $this->sql['delete'] = "DELETE FROM";

        if ($callOrDriver) {
            $this->sql['delete'] .= " ({$this->getDriverSql($callOrDriver)}) ";
        } else {
            $this->sql['delete'] .= " {$this->tableName} ";
        }

        return $this;
    }


    /**
     * 查询
     * @param null $column
     * @param null $callOrDriver
     * @return $this
     */
    public function select($column = null, $callOrDriver = null)
    {
        if ($column === null) {
            $column = $this->tableColumn;
        } else if ($column && is_array($column)) {
            $column = Utils::getInstance()->formatColumn(join(',', $column), $this->joinString);
        }

        $this->sql['select'] .= "SELECT {$column} FROM";

        if ($callOrDriver) {
            $this->sql['select'] .= " ({$this->getDriverSql($callOrDriver)}) ";
        } else {
            $this->sql['select'] .= " {$this->tableName} ";
        }

        return $this;
    }


    /**
     * 设置查询载体
     * @param $carrier
     * @return $this
     */
    public function setCarrier($carrier)
    {
        $this->sql['select'] .= ' ' . Utils::getInstance()->formatColumn($carrier) . ' ';

        return $this;
    }


    /**
     * alias
     * @param $carrier
     * @return $this
     */
    public function alias($carrier)
    {
        $this->sql['select'] .= " AS " . Utils::getInstance()->formatColumn($carrier) . ' ';

        return $this;
    }


    /**
     * JOIN
     * @param $table
     * @param $callOrDriver
     * @param null $location
     * @return $this
     */
    public function join($table, $callOrDriver = null, $location = null)
    {
        $table = Utils::getInstance()->getTableName($table, $this->joinString);

        $currentSql = $this->getSql();

        $this->resetSql();

        if (empty($currentSql)) {
            $this->sql['join'] = " {$location} JOIN {$table}{$this->getDriverSql($callOrDriver)} ";
        } else {
            $this->sql['join'] = "{$currentSql} {$location} JOIN {$table}{$this->getDriverSql($callOrDriver)}";
        }

        return $this;
    }


    /**
     * LEFT JOIN
     * @param $table
     * @param $callOrDriver
     * @return $this
     */
    public function leftJoin($table, $callOrDriver = null)
    {
        $this->join($table, $callOrDriver, 'LEFT');

        return $this;
    }

    /**
     * LEFT JOIN
     * @param $table
     * @param $callOrDriver
     * @return $this
     */
    public function rightJoin($table, $callOrDriver = null)
    {
        $this->join($table, $callOrDriver, 'right');

        return $this;
    }

    /**
     * INNER JOIN
     * @param $table
     * @param $callOrDriver
     * @return $this
     */
    public function innerJoin($table, $callOrDriver = null)
    {
        $this->join($table, $callOrDriver, 'INNER');

        return $this;
    }

    /**
     * FULL  JOIN
     * @param $table :表
     * @param $callOrDriver
     * @return $this
     */
    public function fullJoin($table, $callOrDriver = null)
    {
        $this->join($table, $callOrDriver, 'FULL');

        return $this;
    }


    /**
     * IN
     * @param $name :字段名
     * @param $parameter :参数
     * @param null $match :not
     * @return $this
     */
    public function in($name, $parameter, $match = null)
    {
        $parameter = is_array($parameter) ? $parameter : explode(' ', $parameter);

        $parameterStr = $this->makeInData($name, $parameter, $match);

        $name = Utils::getInstance()->formatColumn($name);

        $this->where("{$name} {$parameterStr}");

        return $this;
    }

    /**
     * 生成in数据
     * @param $name
     * @param $parameter
     * @param null $match
     * @return string
     */
    private function makeInData($name, $parameter, $match = null)
    {
        $parameterStr = "{$match} IN (";

        if (empty($parameter)) return $parameterStr . ")";

        foreach ($parameter as $value) {

            $optionsKey = $this->getOptionsKey($name);

            $parameterStr .= ":{$optionsKey},";

            $this->addOptions($value, $optionsKey);
        }

        $parameterStr = StrCharacter::getInstance()->deleteStringLast($parameterStr);

        return $parameterStr . ")";
    }


    /**
     * not in
     * @param $name :字段名
     * @param $parameter :参数
     * @return $this
     */
    public function notIn($name, $parameter)
    {
        $this->in($name, $parameter, 'NOT');

        return $this;
    }

    /**
     * union
     * @param $callOrDriver
     * @param string $param
     * @return $this
     */
    public function union($callOrDriver, $param = '')
    {
        $currentSql = $this->getSql();

        $this->resetSql();

        if (empty($currentSql)) {
            $this->sql['select'] = " UNION {$param} {$this->getDriverSql($callOrDriver)} ";
        } else {
            $this->sql['select'] = "({$currentSql}) UNION {$param} ({$this->getDriverSql($callOrDriver)})";
        }

        return $this;
    }


    /**
     * 给表添加字段
     * @param $fieldName
     * @param $fieldType
     * @return $this
     */
    public function alterAdd($fieldName, $fieldType)
    {
        $fieldName = Utils::getInstance()->formatColumn($fieldName, $this->joinString);

        $this->sql['query'] = "ALTER TABLE  ADD {$this->tableName} {$fieldName} $fieldType ";

        return $this;
    }


    /**
     * 删除表字段
     * @param $fieldName
     * @return $this
     */
    public function alterDropColumn($fieldName)
    {
        $fieldName = Utils::getInstance()->formatColumn($fieldName, $this->joinString);

        $this->sql['query'] = "ALTER TABLE ADD {$this->tableName} DROP COLUMN {$fieldName} ";

        return $this;
    }


    /**
     * 修改表字段
     * @param $fieldName
     * @param $fieldType
     * @return $this
     */
    public function alterModify($fieldName, $fieldType)
    {
        $fieldName = Utils::getInstance()->formatColumn($fieldName, $this->joinString);

        $this->sql['query'] = "ALTER TABLE {$this->tableName} ALTER COLUMN {$fieldName} $fieldType ";

        return $this;
    }

    /**
     * on
     * @param $name
     * @param null $values
     * @param string $expression
     * @return $this
     */
    public function on($name, $values = null, $expression = '=:')
    {
        if (!is_null($name)) {
            if (is_null($values)) {
                if (!$this->sql['on']) {
                    $this->sql['on'] = "ON {$name} ";
                } else {
                    $this->sql['on'] .= "AND {$name} ";
                }
            } else {
                $optionsKey = $this->getOptionsKey($name);

                $name = Utils::getInstance()->formatColumn($name, $this->joinString);

                if (!$this->sql['on']) {
                    $this->sql['on'] = "ON {$name}{$expression}{$optionsKey} ";
                } else {
                    $this->sql['on'] .= "AND {$name}{$expression}{$optionsKey} ";
                }
                $this->addOptions($values, $optionsKey);
            }
        }
        return $this;
    }


    /**
     * 条件
     * @param $name
     * @param $values
     * @param string $expression
     * @return $this
     */
    public function where($name, $values = null, $expression = '=:')
    {
        if (!is_null($name)) {
            if (is_null($values)) {
                if (!$this->sql['where']) {
                    $this->sql['where'] = "WHERE {$name} ";
                } else {
                    $this->sql['where'] .= "AND {$name} ";
                }
            } else {
                $optionsKey = $this->getOptionsKey($name);

                $name = Utils::getInstance()->formatColumn($name, $this->joinString);

                if (!$this->sql['where']) {
                    $this->sql['where'] = "WHERE {$name}{$expression}{$optionsKey} ";
                } else {

                    $this->sql['where'] .= "AND {$name}{$expression}{$optionsKey} ";
                }
                $this->addOptions($values, $optionsKey);
            }
        }
        return $this;
    }


    /**
     * 排序
     * @param $column
     * @param string $mode
     * @return $this
     */
    public function order($column, $mode = 'ASC')
    {
        $this->sql['order'] = "ORDER BY {$column} {$mode} ";
        return $this;
    }


    /**
     * 分组
     * @param $column
     * @return $this
     */
    public function group($column)
    {
        $this->sql['group'] = "GROUP BY {$column} ";
        return $this;
    }

    /**
     * 分页
     * @param $page
     * @param null $total
     * @return $this
     */
    public function limit($page, $total = null)
    {
        return $this;
    }


    /**
     * drop
     * @param string $name
     * @param string $type
     * @return $this
     */
    public function drop($name = '', $type = 'TABLE')
    {
        $name = $name ? $name : $this->tableName;

        $this->sql['query'] = "DROP {$type} {$name}";

        return $this;
    }


    /**
     * dropTable
     * @return $this
     */
    public function dropTable()
    {
        $this->drop($this->tableName, 'TABLE');

        return $this;
    }

    /**
     * dropDatabase
     * @param $database
     * @return $this
     */
    public function dropDatabase($database)
    {
        $database = Utils::getInstance()->formatColumn($database, $this->joinString);

        $this->drop($database, 'DATABASE');

        return $this;
    }

    /**
     * isNotNull
     * @param $name
     * @return $this
     */
    public function isNotNull($name)
    {
        $name = Utils::getInstance()->formatColumn($name, $this->joinString);

        $this->where("{$name} IS NOT NULL ");

        return $this;
    }

    /**
     * isNull
     * @param $name
     * @return $this
     */
    public function isNull($name)
    {
        $name = Utils::getInstance()->formatColumn($name, $this->joinString);

        $this->where("{$name} IS NULL ");

        return $this;
    }


    /**
     * 模糊查询
     * @param $name
     * @param $value
     * @param int $expression
     * @return $this
     */
    public function like($name, $value, $expression = 3)
    {
        switch ($expression) {
            case 1:
                $expressionStr = "$value%";
                break;
            case 2:
                $expressionStr = "%$value";
                break;
            case 3:
                $expressionStr = "%$value%";
                break;
            default:
                $expressionStr = "$value";
        }

        $this->where($name, $expressionStr, ' LIKE :');

        return $this;
    }


    /**
     * having
     * @param $having
     * @return $this
     */
    public function having($having)
    {
        $this->sql['having'] .= " HAVING $having ";
        return $this;
    }


    /**
     * getTables
     * @param $type
     * @param $database
     * @return Driver
     */
    abstract public function getTables($type, $database);


    /**
     * getTableStructure
     * @param $type
     * @param $database
     * @param $table
     * @return Driver
     */
    abstract public function getTableStructure($type, $database, $table);

    /**
     * 获取创建sql
     * @param $type
     * @param $database
     * @param $table
     * @return Driver
     */
    abstract public function getTableCreateSql($type, $database, $table);


    /**
     * 获取语句
     * @return string
     */
    public function getSql()
    {
        return str_replace('  ', ' ', join(' ', $this->sql));
    }

    /**
     * 获取语句
     * @return string
     */
    public function print()
    {
        $sql = $this->getSql();

        $options = $this->getOptions();

        foreach ($options as $name => $value) {
            if (!is_numeric($value)) $value = "'{$value}'";

            $name = ":{$name}";

            $position = strpos($sql, $name);

            if (is_int($position)) {
                $sql = substr_replace($sql, $value, $position, strlen($name));
            }
        }

        return $sql;
    }


    /**
     * 获取预先执行参数
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     * 生成OptionsKey，防止多个键重复
     * @param $name
     * @return string
     */
    public function getOptionsKey($name)
    {
        $name = str_replace(['.', '='], '_', $name);

        return $name . count($this->getOptions());
    }

    /**
     * 添加options
     * @param $value
     * @param null $key
     * @return Driver
     */
    public function addOptions($value, $key = null)
    {
        $key ? $this->options[$key] = $value : $this->options[] = $value;

        return $this;
    }

    /**
     * @return Statement
     */
    public function getStatement()
    {
        $statement = $this->db->query($this->getSql());

        $statement->setOptions($this->getOptions());

        return $statement;
    }

    /**
     * 除过select都用这个执行
     * @param int $insetId
     * @return bool
     * @throws Exception
     */
    public function execute(&$insetId = -1)
    {
        $statement = $this->getStatement();

        $result = $statement->execute();

        if ($result && $insetId !== -1) $insetId = $this->db->getConnect()->lastInsertId();

        return $result;
    }
}

