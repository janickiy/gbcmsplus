<?php

namespace mcms\user\components\events;

use Yii;
use mcms\user\models\User;

class EventPasswordGenerateLinkSended extends AbstractUserEvents
{

    public $user;

    /**
     * EventPasswordGenerateLinkSended constructor.
     * @param $user
     */
    public function __construct(?User $user)
    {
        $this->user = $user;
    }

    public function getModelId()
    {
        return $this->user->id ?? null;
    }

    public function getOwner()
    {
        return $this->user;
    }

    function getEventName()
    {
        return Yii::_t('users.events.event_users_password_generate_link_sended');
    }

}
