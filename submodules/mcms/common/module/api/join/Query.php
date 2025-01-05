<?php

namespace mcms\common\module\api\join;

use yii\db\Query as DbQuery;

class Query
{
    protected $leftQuery;

    protected $joinTable;
    protected $joinComparator;
    protected $joinFiled;
    protected $joinType = 'LEFT JOIN';

    protected $joinOn = '';

    protected $rightTableFields;

    protected $rightTable;
    protected $rightTableColumn;
    protected $rightTableAlias;

    public function __construct(DbQuery $leftQuery, $tableName, array $joinParameters, array $rightTableFields)
    {
        list($this->joinType, $this->joinOn, $this->joinComparator, $this->rightTableAlias) = $joinParameters;
        $this->leftQuery = $leftQuery;
        $this->joinTable = $tableName;
        $this->rightTableFields = $this->validateRightTableFieldsArray($rightTableFields);
    }

    private function validateRightTableFieldsArray(array $rightTableFields)
    {
        foreach ($rightTableFields as $alias => $value) {
            if (!is_string($alias) || !is_string($value)) throw new InvalidRightTableFieldsException;
        }

        return $rightTableFields;
    }

    public function join()
    {
        return $this
            ->leftQuery
            ->join(
                $this->joinType,
                sprintf('%s %s', $this->rightTable, $this->rightTableAlias),
                sprintf('%s %s %s.%s', $this->joinOn, $this->joinComparator, $this->rightTableAlias, $this->rightTableColumn)
            )
            ->addSelect($this->rightTableFields);
    }

    /**
     * @param mixed $rightTable
     * @return $this
     */
    public function setRightTable($rightTable)
    {
        $this->rightTable = $rightTable;
        return $this;
    }

    /**
     * @param mixed $rightTableColumn
     * @return $this
     */
    public function setRightTableColumn($rightTableColumn)
    {
        $this->rightTableColumn = $rightTableColumn;
        return $this;
    }
}