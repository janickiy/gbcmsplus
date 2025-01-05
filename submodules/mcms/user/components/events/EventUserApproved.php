<?php

namespace mcms\user\components\events;

use Yii;
use mcms\user\models\User;

class EventUserApproved extends AbstractUserEvents
{
    public $user;
    public $referralLink;

    /**
     * EventUserApproved constructor.
     * @param User $user
     */
    public function __construct(?User $user = null, ?string $referralLink = null)
    {
        $this->user = $user;
        $this->referralLink = $referralLink;
    }

    public function getModelId()
    {
        return $this->user ? $this->user->id : null;
    }

    function getEventName()
    {
        return Yii::_t('users.events.user_approved');
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

    public function labels()
    {
        return [
            'referralLink' => Yii::_t('users.forms.user_referral_link')
        ];
    }
}