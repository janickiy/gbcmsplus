<?php

namespace mcms\user\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\module\api\join\Query;
use mcms\user\models\Role;
use yii\data\ActiveDataProvider;
use \mcms\user\models\User as UserModel;
use yii\helpers\ArrayHelper;

class UsersByRoles extends ApiResult
{
    function init($params = [])
    {
        // TRICKY!! ЕСЛИ ПЕРЕДАТЬ ПУСТОЙ МАССИВ РОЛЕЙ, ТО ВЕРНЕТСЯ МАССИВ ВСЕХ ЮЗЕРОВ.
        // ВОЗМОЖНО ЭТОТ МОМЕНТ НАДО ПОПРАВИТЬ, НО НА ТЕКУЩИЙ МОМЕНТ НЕТ ВРЕМЕНИ ТЕСТИТЬ ВЕСЬ ПРОЕКТ

        $pagination = isset($params['pagination']) ? ['pagination' => $params['pagination']] : [];
        $sort = isset($params['sort']) ? ['sort' => $params['sort']] : [];
        unset($params['pagination']);
        unset($params['sort']);
        $roles = $params;

        if (count($roles)) foreach ($roles as &$role) {
            if ($role instanceof Role) {
                $role = $role->item_name;
            }
        }

        $users = UserModel::find()
            ->innerJoin('auth_assignment', UserModel::tableName() . '.id = ' . 'auth_assignment.user_id');

        if (count($roles)) $users->where(['auth_assignment.item_name' => $roles]);

        $this->setDataProvider(new ActiveDataProvider(ArrayHelper::merge([
            'query' => $users,
        ], array_merge($pagination, $sort))));

        $this->setResultTypeArray();
    }

    /**
     * @param Query $query
     */
    public function join(Query &$query)
    {
        $query
            ->setRightTable('auth_assignment')
            ->setRightTableColumn('user_id')
            ->join();
    }
}