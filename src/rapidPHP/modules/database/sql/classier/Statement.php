<?php


namespace rapidPHP\modules\database\sql\classier;


use Exception;
use PDO;
use PDOStatement;
use rapidPHP\modules\common\classier\Build;
use rapidPHP\modules\core\classier\Model;
use rapidPHP\modules\reflection\classier\Utils as ReflectionUtils;

class Statement
{
    private $statement;

    /**
     * @var array|null
     */
    private $options;

    /**
     * Statement constructor.
     * @param PDOStatement $statement
     * @param array $options
     */
    public function __construct(PDOStatement $statement, $options = [])
    {
        $this->statement = $statement;
        $this->options = $options;
    }

    /**
     * 获取statement
     * @return bool|PDOStatement
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * @param array|null $options
     */
    public function setOptions(?array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return array|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * bindColumn
     * @return bool
     */
    public function bindColumn()
    {
        return $this->statement->bindColumn(...func_get_args());
    }

    /**
     * bindValue
     * @return bool
     */
    public function bindValue()
    {
        return $this->statement->bindColumn(...func_get_args());
    }

    /**
     * bindParam
     * @return bool
     */
    public function bindParam()
    {
        return $this->statement->bindParam(...func_get_args());
    }

    /**
     * execute
     * @param bool $isCloseCursor
     * @return bool
     */
    public function execute($isCloseCursor = true)
    {
        $result = $this->statement->execute($this->options);

        if ($isCloseCursor) $this->statement->closeCursor();

        return $result;
    }

    /**
     * 获取一条
     * @param Model|string|null $model
     * @return mixed|object|void|null
     * @throws Exception
     */
    public function fetch($model = null)
    {
        if (!$this->execute(false)) return null;

        $data = $this->statement->fetch(PDO::FETCH_ASSOC);

        $this->statement->closeCursor();

        if (empty($data)) return null;

         Build::getInstance()->toTypeConvertByAO($data);

        if (empty($model)) return $data;

        return ReflectionUtils::getInstance()->toObject($model, $data);
    }

    /**
     * 获取value
     * @param $name
     * @return mixed|null
     */
    public function fetchValue($name)
    {
        if (!$this->execute(false)) return null;

        $data = $this->statement->fetch(PDO::FETCH_ASSOC);

        $this->statement->closeCursor();

        if (empty($data)) return null;

        return isset($data[$name]) ? $data[$name] : null;
    }

    /**
     * 获取全部
     * @param Model|string|null $model
     * @return mixed|object|void|null
     * @throws Exception
     */
    public function fetchAll($model = null)
    {
        if (!$this->execute(false)) return null;

        $data = $this->statement->fetchAll(PDO::FETCH_ASSOC);

        $this->statement->closeCursor();

        if (empty($data)) return null;

        Build::getInstance()->toTypeConvertByAO($data);

        if (empty($model)) return $data;

        foreach ($data as $index => $datum) {
            $data[$index] = ReflectionUtils::getInstance()->toObject($model, $data);
        }

        return $data;
    }

}