<?php

namespace admin\modules\credits\events;

use admin\modules\credits\models\CreditTransaction;
use mcms\common\event\Event;
use Yii;

/**
 * Пришла выплата из MGMP
 */
class CreditExternalPaymentEvent extends Event
{
    /** @var CreditTransaction */
    public $payment;

    /**
     * @param CreditTransaction|null $payment
     */
    public function __construct($payment = null)
    {
        $this->payment = $payment;
    }

    /**
     * @inheritdoc
     */
    public function getModelId()
    {
        return $this->payment->id;
    }

    /**
     * @inheritdoc
     */
    public function getOwner()
    {
        return $this->payment->credit->user;
    }

    /**
     * @inheritdoc
     */
    public static function getUrl($id = null)
    {
        $transaction = $id ? CreditTransaction::findOne($id) : null;
        return $transaction ? ['/credits/credits/view/', 'id' => $transaction->credit_id] : ['/credits/credits/index/'];
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return Yii::_t('credits.events.external_payment');
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalReplacements()
    {
        return [
            'credit.id' => $this->payment->credit->id,
            'credit.amount' => Yii::$app->formatter->asPrice($this->payment->credit->amount, $this->payment->credit->currency),
            'payment.amount' => Yii::$app->formatter->asPrice($this->payment->amount, $this->payment->credit->currency),
        ];
    }
}
