<?php

namespace mcms\user\components\events;

use Yii;
use mcms\user\models\User;

class EventStatusChanged extends AbstractUserEvents
{
    public $user;

    /**
     * EventStatusChanged constructor.
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
        return Yii::_t('users.events.status_changed');
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