<?php

namespace mcms\user\components\events;

use Yii;
use mcms\user\models\User;

/**
 * отпрака нового(сегенерированного) пароля
 * Class EventNewPasswordSent
 * @package mcms\user\components\events
 */
class EventNewPasswordSent extends AbstractUserEvents
{
    public $user;
    public $password;

    /**
     * EventPasswordChanged constructor.
     * @param $user
     * @param $password
     */
    public function __construct(User $user = null, $password = null)
    {
        $this->user = $user;
        $this->password = $password;
    }

    function getEventName()
    {
        return Yii::_t('users.events.new_password_sent');
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