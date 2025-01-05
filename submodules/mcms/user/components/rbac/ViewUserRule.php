<?php

namespace mcms\user\components\rbac;

use mcms\user\components\api\NotAvailableUserIds;
use mcms\user\models\User;
use mcms\user\Module;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\Rule;

/**
 * Есть ли доступ для просмотра данных связанных с определенным пользователем.
 * Параметры:
 * int $userId ID пользователя, к которому нужно получить доступ
 * bool $canViewUserWithoutManager Должен ли быть доступ к пользователям, к которым не привязан менеджер.
 * Параметр сделан для того, что бы менеджер мог редактировать пользователей без привязанного менеджера в списке пользователей
 * и не мог управлять их записями, например тикетами, лендами и прочим
 */
class ViewUserRule extends Rule
{

    public $name = 'UsersViewUserRule';
    public $description = 'Can view user';

    public function execute($user, $item, $params)
    {
        $notAvailableUserIds = (new NotAvailableUserIds([
            'userId' => Yii::$app->user->id,
        ]))->getResult();

        $userId = ArrayHelper::getValue($params, 'userId');
        $canViewUserWithoutManager = ArrayHelper::getValue($params, 'canViewUserWithoutManager', false);
        $user = User::findOne(['id' => $userId]);

        return
            // Пользователь не находится в игнор-листе
            !array_key_exists($userId, $notAvailableUserIds)
            // Пользователь привязан к менеджеру или менеджер имеет право просматривать всех пользователей
            && (
                Yii::$app->user->can(Module::PERMISSION_CAN_MANAGE_ALL_USERS)
                || $user->manager_id == Yii::$app->user->id
                || ($canViewUserWithoutManager && !$user->manager_id && $user->hasRole(Module::PARTNER_ROLE))
            );
    }
}
