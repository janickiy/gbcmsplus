<?php

namespace mcms\common\module\api;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class JoinApi
{
    /**
     * @param ActiveQuery $activeQuery
     * @param JoinCondition $joinCondition
     * @param array $selectFields
     * @return ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public static function join(ActiveQuery $activeQuery, JoinCondition $joinCondition, array $selectFields)
    {
        $activeQuery->join(
            $joinCondition->getJoinType(),
            $joinCondition->getTablesString(),
            $joinCondition->getComparatorString()
        );

        if ($activeQuery->select === null && $activeQuery->modelClass !== null) {
            /** @var ActiveRecord $modelClass */
            $modelClass = \Yii::createObject($activeQuery->modelClass);

            $activeQuery->select = $modelClass->attributes();
        }


        if ($activeQuery->select !== null && count($activeQuery->select) > 0) {
            $activeQuery->select = array_map(function ($field) use ($joinCondition) {
                if (strpos($field, '(') !== false) return $field;
                return strtr('`:leftTable`.`:column` AS :leftColumnAlias', [
                    ':leftTable' => $joinCondition->getLeftTable(),
                    ':column' => $field,
                    ':leftColumnAlias' => $joinCondition->getLeftTable() . '_' . $field
                ]);
            }, $activeQuery->select);
        }

        $activeQuery->addSelect(array_map(function ($selectField) use ($joinCondition) {
            return sprintf(
                '`%s`.`%s` AS `%s_%s`',
                $joinCondition->getRightTable(),
                $selectField,
                $joinCondition->getRightTable(),
                $selectField
            );
        }, $selectFields));


        if (is_array($activeQuery->where)) {
            $activeQuery->where = static::changeFieldsArray($activeQuery->where, $joinCondition);
        }

        $activeQuery->groupBy = static::changeGroupBy($activeQuery, $joinCondition);

        return $activeQuery;

    }

    /**
     * @param ActiveQuery $activeQuery
     * @param JoinCondition $joinCondition
     * @return array|string[]
     */
    private static function changeGroupBy(ActiveQuery $activeQuery, JoinCondition $joinCondition)
    {
        if (count($activeQuery->groupBy)) {
            $activeQuery->groupBy = array_map(function ($field) use ($joinCondition) {
                return sprintf('`%s`.`%s`', $joinCondition->getRightTable(), $field);
            }, $activeQuery->groupBy);
        }

        return $activeQuery->groupBy;
    }

    private static function changeFieldsArray(array $fields, JoinCondition $joinCondition)
    {
        $newFieldsArray = [];
        if (count($fields)) foreach ($fields as $field => $value) {
            if (is_numeric($field)) {
                $newFieldsArray[] = $value;
            } else {
                $newFieldsArray[sprintf('`%s`.`%s`', $joinCondition->getRightTable(), $field)] = $value;
            }
        }

        return $newFieldsArray;
    }
}