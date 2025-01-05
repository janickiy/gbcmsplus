<?php

namespace mcms\user\components\events;

use Yii;

/**
 * Class EventUserInvited
 * @package mcms\user\components\events
 *
 * Пользователь зарегался по ссылке
 */
class EventUserInvited extends EventAbstractRegistered
{
    /**
     * @return string
     */
    function getEventName()
    {
        return Yii::_t('users.events.auth_registered_invited');
    }

    /**
     * @param int $id
     * @return array
     */
    public static function getUrl(?int $id = null)
    {
        return ['/users/users/update/', 'id' => $id];
    }
}