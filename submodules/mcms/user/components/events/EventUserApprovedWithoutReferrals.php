<?php

namespace mcms\user\components\events;

use mcms\common\event\Event;
use mcms\user\models\User;
use Yii;

class EventUserApprovedWithoutReferrals extends Event
{
    public $user;

    /**
     * EventUserApproved constructor.
     * @param User $user
     */
    public function __construct(?User $user = null)
    {
        $this->user = $user;
    }

    public function getModelId()
    {
        return $this->user;
    }

    function getEventName()
    {
        return Yii::_t('users.events.user_approved_without_referrals');
    }

    public static function getUrl(?int $id = null)
    {
        if ($id && Yii::$app->user->identity->isNotCurrentUser($id)) {
            return ['/users/users/update/', 'id' => $id];
        }
        return Event::getUrl($id);
    }

    public function getOwner()
    {
        return $this->user;
    }

}