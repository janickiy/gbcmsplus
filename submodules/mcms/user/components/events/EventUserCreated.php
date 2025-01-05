<?php

namespace mcms\user\components\events;

use Yii;
use mcms\user\models\User;

class EventUserCreated extends AbstractUserEvents
{

    public $user;
    public $password;

    /**
     * EventUserCreated constructor.
     * @param $user
     * @param $password
     */
    public function __construct(?User $user = null, ?string $password = null)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function getModelId()
    {
        return $this->user ? $this->user->id : null;
    }

    function getEventName()
    {
        return Yii::_t('users.events.user_created');
    }

    public function labels()
    {
        return [
            'password' => Yii::_t('users.replacements.password')
        ];
    }

    public static function getUrl(?int $id = null)
    {
        if ($id && Yii::$app->user->identity->isNotCurrentUser($id)) {
            return ['/users/users/update/', 'id' => $id];
        }
        return null;
    }
}