<?php

namespace mcms\user\components\api;

use mcms\common\module\api\ApiResult;
use mcms\user\models\search\User as UserSearch;
use mcms\user\Module;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Возвращает массив пользователей, которые недоступны для просмотра текущему юзеру
 * Текущий юзер передаем в настройку userId.
 *
 * Class NotAvailableUserIds
 * @package mcms\user\components\api
 */
class NotAvailableUserIds extends ApiResult
{
    private $currentUserId;
    /**
     * хотим ли видеть в результате айди текущего юзера, или его нужно исключить из этого массива?
     * @var bool
     */
    private $skipCurrentUser;
    /**
     * в этом кэше храним список юзеров, всегда включая текущего.
     * И уже потом в зависимости от настройки $skipCurrentUser фильтруем этот массив.
     * @var array
     */
    private static $_rii = [];

    /** @var  \yii\rbac\ManagerInterface */
    private $authManager;

    /**
     * @inheritdoc
     */
    function init($params = [])
    {
        $this->currentUserId = ArrayHelper::getValue($params, 'userId');
        if ($this->currentUserId === null) $this->addError('User is not provided');

        $this->skipCurrentUser = ArrayHelper::getValue($params, 'skipCurrentUser', false);

        $this->authManager = Yii::$app->authManager;
    }

    /**
     * @return array|\int[]
     */
    public function getResult()
    {
        if (isset(self::$_rii[$this->currentUserId])) {
            return $this->unsetCurrentUserId(self::$_rii[$this->currentUserId]);
        }

        // в партнерском кабинете обходимся без ч\с
        if ($this->authManager->checkAccess($this->currentUserId, 'UsersUsersViewPartnerCabinet')) return [];

        $roles = [];

        if (!$this->authManager->checkAccess($this->currentUserId, UserSearch::PERMISSION_VIEW_ROOT_USER)) {
            $roles[] = Module::ROOT_ROLE;
        }
        if (!$this->authManager->checkAccess($this->currentUserId, UserSearch::PERMISSION_VIEW_ADMIN_USER)) {
            $roles[] = Module::ADMIN_ROLE;
        }
        if (!$this->authManager->checkAccess($this->currentUserId, UserSearch::PERMISSION_VIEW_RESELLER_USER)) {
            $roles[] = Module::RESELLER_ROLE;
        }
        if (!$this->authManager->checkAccess($this->currentUserId, UserSearch::PERMISSION_VIEW_PARTNER_USER)) {
            $roles[] = Module::PARTNER_ROLE;
        }

        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('users');
        if (!$this->authManager->checkAccess($this->currentUserId, UserSearch::PERMISSION_VIEW_MANAGER_USER)) {
            foreach ($userModule->getManagerRoles() as $managerRole) {
                $roles[] = $managerRole;
            }
        }

        $ignoreUserIdsQuery = (new Query())
            ->from('auth_assignment')
            ->select('user_id')
            ->where(['item_name' => $roles])
            ->each();

        /** @var array $ignoreUserIdsQuery */
        $ignoreUserIds = ArrayHelper::map($ignoreUserIdsQuery, 'user_id', 'user_id');

        self::$_rii[$this->currentUserId] = $ignoreUserIds;

        $ignoreUserIds = $this->unsetCurrentUserId($ignoreUserIds);

        return $ignoreUserIds;
    }

    /**
     * @param $userIds
     * @return int[]
     */
    private function unsetCurrentUserId($userIds)
    {
        if ($this->skipCurrentUser && isset($userIds[$this->currentUserId])) {
            unset($userIds[$this->currentUserId]);
        }
        return $userIds;
    }
}
