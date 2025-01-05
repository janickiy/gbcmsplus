<?php

namespace admin\modules\alerts\components\events;

class AlertEvent extends BaseAlertEvent
{
    function getEventName()
    {
        return 'Alert';
    }
}