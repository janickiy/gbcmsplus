<?php

namespace admin\modules\credits\events;

use Yii;

/**
 * Кредит одобрен
 */
class CreditApprovedEvent extends AbstractCreditEvent
{
    /**
     * @return string
     */
    public function getEventName()
    {
        return Yii::_t('credits.events.credit_approved');
    }
}
