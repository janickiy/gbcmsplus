<?php

namespace mcms\user\components\events;

use Yii;
use mcms\user\models\User;

class EventUserUpdated extends AbstractUserEvents
{
    public $user;
    public $password;

    /**
     * Пользователь обновлен
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
        return Yii::_t('users.events.user_updated');
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

    public function trigger()
    {
        parent::trigger();

        Yii::$app->getModule('notifications')->api('setViewedByIdEvent', [
            'event' => EventRegisteredHandActivation::class,
            'modelId' => $this->getModelId(),
        ])->getResult();

        Yii::$app->getModule('users')->api('badgeCounters')->invalidateCache();
    }
}