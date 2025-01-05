<?php

namespace mcms\user\components\events;

use Yii;
use mcms\user\models\User;

class EventReferralRegistered extends AbstractUserEvents
{
    public $referrer;

    /**
     * EventAuthRegistered constructor.
     * @param User $owner
     * @param User $referrer
     */
    public function __construct(?User $owner = null, ?User $referrer = null)
    {
        $this->referrer = $referrer;
        $this->setOwner($owner);
    }

    public function getModelId()
    {
        return $this->referrer ? $this->referrer->id : null;
    }

    public static function getUrl(?int $id = null)
    {
        return ['/partners/referrals/income/'];
    }

    function getEventName()
    {
        return Yii::_t('users.events.referral_registered');
    }

}