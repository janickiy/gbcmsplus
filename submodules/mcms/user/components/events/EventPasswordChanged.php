<?php

namespace mcms\user\components\events;

use Yii;
use mcms\user\models\User;

class EventPasswordChanged extends AbstractUserEvents
{
    public $user;
    public $password;

    /**
     * EventPasswordChanged constructor.
     * @param User|null $user
     * @param string|null $password
     */
    public function __construct(?User $user = null, ?string $password = null)
    {
        $this->user = $user;
        $this->password = $password;
    }

    function getEventName()
    {
        return Yii::_t('users.events.password_changed');
    }

    public function getOwner()
    {
        return $this->user;
    }

    public function labels()
    {
        return [
            'password' => Yii::_t('users.replacements.password')
        ];
    }
}