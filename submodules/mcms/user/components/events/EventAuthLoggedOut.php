<?php

namespace mcms\user\components\events;

use Yii;
use mcms\user\models\User;

class EventAuthLoggedOut extends AbstractUserEvents
{
    public $loggedOutUser;

    /**
     * EventAuthLoggedIn constructor.
     * @param $loggedOutUser
     */
    public function __construct(?User $loggedOutUser = null)
    {
        $this->loggedOutUser = $loggedOutUser;
    }

    public function getModelId()
    {
        return $this->loggedOutUser ? $this->loggedOutUser->id : null;
    }

    function getEventName()
    {
        return Yii::_t('users.events.auth_logged_out');
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