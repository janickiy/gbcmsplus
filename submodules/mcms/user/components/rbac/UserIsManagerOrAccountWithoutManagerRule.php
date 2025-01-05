<?php

namespace mcms\user\components\rbac;

use Yii;
use yii\rbac\Rule;

/**
 * Текущий авторизованный пользователь является менеджером указанного аккаунта или к аккаунту не привязан менеджер
 *
 * Параметры:
 * User $user Аккаунт, доступ к которому нужно получить
 */
class UserIsManagerOrAccountWithoutManagerRule extends Rule
{
    const RULE_NAME = 'UserIsManagerOrAccountWithoutManagerRule';
    public $name = UserIsManagerOrAccountWithoutManagerRule::RULE_NAME;
    public $description = 'Текущий авторизованный пользователь является менеджером указанного аккаунта или к аккаунту не привязан менеджер';

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        return !empty($params['user'])
            && (empty($params['user']->manager_id) || $params['user']->manager_id == Yii::$app->user->id);
    }
}