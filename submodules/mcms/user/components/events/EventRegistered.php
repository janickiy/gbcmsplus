<?php

namespace mcms\user\components\events;

use Yii;

class EventRegistered extends EventAbstractRegistered
{
    function getEventName()
    {
        return Yii::_t('users.events.auth_registered_auto-activation');
    }

    public static function getUrl($id = null)
    {
        return ['/users/users/update/', 'id' => $id];
    }

}