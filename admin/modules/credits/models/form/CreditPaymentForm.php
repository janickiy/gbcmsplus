<?php

namespace admin\modules\credits\models\form;

use admin\modules\credits\models\Credit;
use admin\modules\credits\models\CreditTransaction;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Форма создания/изменения выплаты
 */
class CreditPaymentForm extends CreditTransaction
{
    /** @const string */
    const SCENARIO_PAYMENT_CREATE = 'payment_create';
    /** @const string */
    const SCENARIO_PAYMENT_UPDATE = 'payment_update';

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            static::SCENARIO_PAYMENT_CREATE => ['credit_id', 'amount'],
            static::SCENARIO_PAYMENT_UPDATE => ['amount'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->scenario = static::SCENARIO_PAYMENT_CREATE;

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->scenario = static::SCENARIO_PAYMENT_UPDATE;

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        // Реселлер не может создавать выплаты отличные от выплат со своего баланса
        $this->type = static::TYPE_BALANCE_PAYMENT;

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            if (!static::isAvailableCreate($this)) {
                return false;
            }
        } else {
            if (!static::isAvailableUpdate($this)) {
                return false;
            }
        }

        return parent::beforeSave($insert);
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['amount'], 'compare', 'compareValue' => 0, 'operator' => '>', 'type' => 'number'],
            ['amount', 'validateDebt'],
            ['amount', 'validateBalance'],
        ]);
    }

    /**
     * Валидировать задолженность
     * @return bool
     */
    public function validateDebt()
    {
        $debt = $this->credit_id ? $this->credit->getDebt() : null;

        /* Корректировка задолженности в соответствии с суммой редактируемой выплаты.
         * Пример решаемой проблемы: если создать выплату, а затем после неё создать еще одну,
         * то при редактировании предыдущей выплаты может появится ошибка, что указанная сумма больше задолженности */
        if (!$this->isNewRecord) {
            $debt += $this->getOldAttribute('amount');
        }

        // Выплата не может быть больше задолженности
        if ($this->credit_id && $this->amount > $debt) {
            $this->addError('amount', 'Amount cannot be more then ' . Yii::$app->formatter->asDecimal($debt));
            return false;
        }

        return true;
    }

    /**
     * Проверка баланса
     * @return bool
     */
    public function validateBalance()
    {
        // TRICKY Баланс проверяет только при ручно создании выплаты. При импорте транзакций баланс не учитывается,
        // иначе некоторые комиссии не будут сняты до пополнения баланса
        $balance = $this->credit->getBalance();

        /* Корректировка баланса в соответствии с суммой редактируемой выплаты.
         * Пример решаемой проблемы: на счету 0, выплата 50 000, редактируем выплату, пишем 30 000 вместо 50 000.
         * Нам напишет, что не хватает денег на балансе, потому что 0 < 30 000 */
        if (!$this->isNewRecord) {
            $balance += $this->getOldAttribute('amount');
        }

        if ($balance <= 0) {
            $this->addError('amount', Yii::_t('payments.user-payments.error-balance-main'));
            return false;
        }

        if (($balance - $this->amount) < 0) {
            $this->addError('amount', Yii::_t('payments.user-payments.error-balance-insufficient'));
            return false;
        }

        return true;
    }

    /**
     * Поверхностная проверка возможности управления выплатами кредита.
     * Для полной проверки возможности нужна информация о транзакции,
     * поэтому нужно использовать методы @param Credit $credit
     * @return bool
     * @see isAvailableCreate() и @see isAvailableUpdate()
     */
    public static function isAvailableByCredit(Credit $credit)
    {
        return $credit->status === Credit::STATUS_ACTIVE;
    }

    /**
     * Проверка возможности создания выплаты
     * @param CreditTransaction $payment
     * @return bool
     */
    public static function isAvailableCreate(CreditTransaction $payment)
    {
        return static::isAvailableSave($payment);
    }

    /**
     * Проверка возможности изменения выплаты
     * @param CreditTransaction $payment
     * @return bool
     */
    public static function isAvailableUpdate(CreditTransaction $payment)
    {
        return
            static::isAvailableSave($payment)
            // Нельзя редактировать выплату если после её создания была списана комиссия
            && !$payment->credit
                ->getTransactions()
                ->andWhere(['type' => CreditTransaction::FEE_TYPES])
                ->andWhere(['>=', 'created_at', $payment->created_at])
                ->exists();
    }

    /**
     * Доступно ли сохранение выплаты
     * Для полной проверки возможности нужно знать тип операции (создать или обновить),
     * поэтому нужно использовать методы @param CreditTransaction $payment
     * @return bool
     * @see isAvailableCreate() и @see isAvailableUpdate()
     */
    protected static function isAvailableSave(CreditTransaction $payment)
    {
        return static::isAvailableByCredit($payment->credit)
            && in_array($payment->type, CreditTransaction::PAYMENT_TYPES);
    }
}
