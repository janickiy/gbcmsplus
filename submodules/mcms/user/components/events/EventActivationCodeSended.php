<?php

namespace mcms\user\components\events;

use Yii;
use mcms\user\models\User;

class EventActivationCodeSended extends AbstractUserEvents
{

    public $user;
    public $activationCode;

    /**
     * EventActivationCodeSended constructor.
     * @param $user
     * @param $activationCode
     */
    public function __construct(?User $user = null, string $activationCode = '')
    {
        $this->user = $user;
        $this->activationCode = $activationCode;
    }

    public function getOwner()
    {
        return $this->user;
    }

    public function getModelId()
    {
        return $this->user ? $this->user->id : null;
    }

    function getEventName()
    {
        return Yii::_t('users.events.activation_code_sended');
    }

    public function labels()
    {
        return [
            'activationCode' => Yii::_t('users.forms.activation_code')
        ];
    }
}
