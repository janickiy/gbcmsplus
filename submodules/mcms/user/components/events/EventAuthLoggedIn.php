<?php

namespace mcms\user\components\events;

use Yii;
use mcms\user\models\User;

class EventAuthLoggedIn extends AbstractUserEvents
{
    public $loggedInUser;
    public $ip;
    public $userAgent;

    /**
     *
     * @param User|null $loggedInUser
     */
    public function __construct(?User $loggedInUser = null)
    {
        $this->loggedInUser = $loggedInUser;
        $this->ip = Yii::$app->request->userIP;
        $this->userAgent = Yii::$app->request->userAgent;
    }

    public function getModelId()
    {
        return $this->loggedInUser ? $this->loggedInUser->id : null;
    }

    public function getEventName()
    {
        return Yii::_t('users.events.auth_logged_in');
    }

    /**
     * @param int|null $id
     * @return array|null
     */
    public static function getUrl(?int $id = null)
    {
        if ($id && Yii::$app->user->identity->isNotCurrentUser($id)) {
            return ['/users/users/update/', 'id' => $id];
        }
        return null;
    }
}