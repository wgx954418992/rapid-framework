<?php


namespace rapidPHP\modules\database\sql\classier;


use PDO;

class Statement
{

    private $isSuccess;

    /**
     * @var \PDOStatement
     */
    private $statement;

    /**
     * Statement constructor.
     * @param \PDOStatement $statement
     */
    public function __construct(\PDOStatement $statement, array $options)
    {
        $this->isSuccess = $statement->execute($options);

        $this->statement = $statement;
    }

    /**
     * 除过select都用这个执行
     * @param array $options
     * @param $insetId
     * @return bool
     */
    public function getExecute()
    {
        $this->statement->closeCursor();

        return $this->isSuccess;
    }

    /**
     * select用这个执行
     * @param array $options
     * @param int $mode
     * @return Result
     */
    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        return $this->statement->fetch($fetch_style, $cursor_orientation, $cursor_offset);
    }

    public function fetchAll($fetch_style, $fetch_argument, array $ctor_args)
    {
        $data = $this->statement->fetchAll($fetch_style, $fetch_argument, $ctor_args);

        $this->statement->closeCursor();

        return $data;
    }

}