<?php

namespace mcms\user\components\events;

use Yii;
use mcms\user\models\User;

class EventUserBlocked extends AbstractUserEvents
{
    public $user;

    /**
     * EventUserBlocked constructor.
     * @param User $user
     */
    public function __construct(?User $user = null)
    {
        $this->user = $user;
    }

    public function getModelId()
    {
        return $this->user ? $this->user->id : null;
    }

    function getEventName()
    {
        return Yii::_t('users.events.user_blocked');
    }

    public static function getUrl(?int $id = null)
    {
        if ($id && Yii::$app->user->identity->isNotCurrentUser($id)) {
            return ['/users/users/update/', 'id' => $id];
        }
        return null;
    }

    public function getOwner()
    {
        return $this->user;
    }
}