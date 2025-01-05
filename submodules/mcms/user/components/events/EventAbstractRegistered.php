<?php

namespace mcms\user\components\events;

use Yii;
use mcms\user\models\User;

abstract class EventAbstractRegistered extends AbstractUserEvents
{
    public $user;

    /**
     * EventAuthRegistered constructor.
     * @param $user
     */
    public function __construct(?User $user)
    {
        $this->user = $user;
    }

    public function getModelId()
    {
        return $this->user->id;
    }

    public function getOwner()
    {
        return $this->user;
    }

    public function incrementBadgeCounter()
    {
        return $this->user->isModerationByHand();
    }

    public static function getUrl(?int $id = null)
    {
        if (Yii::$app->user->identity->isNotCurrentUser($id)) {
            return ['/users/users/view/', 'id' => $id];
        }
        return null;
    }
}