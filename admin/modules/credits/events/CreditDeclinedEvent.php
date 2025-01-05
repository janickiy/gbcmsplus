<?php

namespace admin\modules\credits\events;

use Yii;

/**
 * Кредит отклонен
 */
class CreditDeclinedEvent extends AbstractCreditEvent
{
    /**
     * @return string
     */
    public function getEventName()
    {
        return Yii::_t('credits.events.credit_declined');
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalReplacements()
    {
        return array_merge(
            parent::getAdditionalReplacements(),
            ['credit.declineReason' => $this->credit->decline_reason]
        );
    }
}
