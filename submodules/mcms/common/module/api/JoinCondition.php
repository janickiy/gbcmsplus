<?php

namespace mcms\common\module\api;

class JoinCondition
{
    private $comparator;

    private $leftTableColumn;
    private $leftTable;
    private $joinType = 'INNER JOIN';

    public $rightTableColumn;
    public $rightTable;
    private $rightTableAlias;

    /**
     * JoinCondition constructor.
     * @param $leftTable
     * @param array $relation
     */
    public function __construct($leftTable, array $relation, $rightTableAlias)
    {
        $this->leftTable = $leftTable;
        list($this->comparator, $this->leftTableColumn) = $relation;
        $this->rightTableAlias = $rightTableAlias;
    }

    /**
     * @param mixed $rightTableAlias
     * @return $this
     */
    private function setRightTableAlias($rightTableAlias)
    {
        $this->rightTableAlias = $rightTableAlias;
        return $this;
    }

    /**
     * @param $rightTable
     * @return $this
     */
    public function setRightTable($rightTable)
    {
        $this->rightTable = $rightTable;
        return $this;
    }

    /**
     * @param $rightTableColumn
     * @return $this
     */
    public function setRightTableColumn($rightTableColumn)
    {
        $this->rightTableColumn = $rightTableColumn;
        return $this;
    }

    public function setJoinType($joinType)
    {
        $this->joinType = $joinType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJoinType()
    {
        return $this->joinType;
    }

    public function getTablesString()
    {
        return sprintf('%s %s', $this->rightTable, $this->getRightTable());
    }

    public function getRightTable()
    {
        return $this->rightTableAlias;
    }

    public function getRightTableJoinString()
    {
        return sprintf('%s %s', $this->rightTable, $this->rightTableAlias);
    }

    function getComparatorString()
    {
        return sprintf(
            "`%s`.`%s` %s `%s`.`%s`",
            $this->leftTable,
            $this->leftTableColumn,
            $this->comparator,
            $this->getRightTable(),
            $this->rightTableColumn
        );
    }

    /**
     * @return mixed
     */
    public function getLeftTableColumn()
    {
        return $this->leftTableColumn;
    }

    /**
     * @return mixed
     */
    public function getLeftTable()
    {
        return $this->leftTable;
    }
}