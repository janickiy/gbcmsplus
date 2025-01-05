<?php

namespace admin\modules\alerts\components\events;

class InfoEvent extends BaseAlertEvent
{
    function getEventName()
    {
        return 'Info';
    }
}