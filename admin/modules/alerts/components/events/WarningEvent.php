<?php

namespace admin\modules\alerts\components\events;

class WarningEvent extends BaseAlertEvent
{
    function getEventName()
    {
        return 'Warning';
    }
}