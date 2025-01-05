<?php

namespace admin\modules\credits\models;

use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the ActiveQuery class for [[Credit]].
 *
 * @see Credit
 * @property Credit|string $modelClass
 */
class CreditQuery extends ActiveQuery
{
    /**
     * Добавляет в модель Credit поля
     * debtSum
     *
     * @return CreditQuery
     */
    public function withTransactionsSum()
    {
        // TODO TRICKY ЗАПРОС DEBT ДУБЛИРУЕТСЯ В Credit::getDebt()
        // для подсчета остатка по кредиту
        $subQuery = CreditTransaction::find()
            ->select([
                'credit_id',
                'debt' => new Expression(
                    'SUM(IF(type = ' . CreditTransaction::TYPE_ACCRUE_AMOUNT . ', amount, 0)) - ' .
                    'SUM(IF(type IN (' . implode(',', CreditTransaction::PAYMENT_TYPES) . '), amount, 0))'
                ),
                'pay' => new Expression('SUM(IF(type IN (' . implode(',', CreditTransaction::PAYMENT_TYPES) . '), amount, 0))'),
                'fee' => new Expression('SUM(IF(type IN (' . implode(',', CreditTransaction::FEE_TYPES) . '), amount, 0))'),
                'maxPayTime' => new Expression('MAX(IF(type IN (' . implode(',', CreditTransaction::PAYMENT_TYPES) . '), created_at, null))')
            ])
            ->groupBy('credit_id');

        $this->addSelect([
            'debtSum' => new Expression('tranSum.debt'),
            'paySum' => new Expression('tranSum.pay'),
            'feeSum' => new Expression('tranSum.fee'),
            'maxPayTime' => new Expression('tranSum.maxPayTime'),
            Credit::tableName() . '.*'
        ]);

        $this->leftJoin(['tranSum' => $subQuery], 'tranSum.credit_id = ' . Credit::tableName() . '.id');

        return $this;
    }
}
