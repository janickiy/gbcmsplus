<?php

namespace mcms\user\components\events;

use Yii;

class EventRegisteredHandActivation extends EventAbstractRegistered
{
    function getEventName()
    {
        return Yii::_t('users.events.auth_registered_hand-activation');
    }

    public function trigger()
    {
        parent::trigger();
        Yii::$app->getModule('users')->api('badgeCounters')->invalidateCache();
    }
}