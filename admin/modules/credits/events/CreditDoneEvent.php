<?php

namespace admin\modules\credits\events;

use Yii;

/**
 * Кредит погашен
 */
class CreditDoneEvent extends AbstractCreditEvent
{
    /**
     * @return string
     */
    public function getEventName()
    {
        return Yii::_t('credits.events.credit_closed');
    }
}
