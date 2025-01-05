<?php

namespace mcms\user\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\module\api\join\Query;
use mcms\user\models\Role;
use yii\data\ActiveDataProvider;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class User extends ApiResult
{

    protected $skipCurrentUser = false;
    protected $ignoreNotAvailableUsers = false;
    protected $onlyActiveUsers = false;

    protected $_searchModel;

    function init($params = [])
    {
        $this->_searchModel = new \mcms\user\models\search\User();
        $this->prepareDataProvider($this->_searchModel, $params);
        $this->skipCurrentUser = in_array('skipCurrentUser', $params);
        $this->ignoreNotAvailableUsers = !!ArrayHelper::getValue($params, 'ignoreNotAvailableUsers');
        $this->onlyActiveUsers = !!ArrayHelper::getValue($params, 'onlyActiveUsers');
    }

    public function getSearchModel()
    {
        return $this->_searchModel;
    }

    public function getUserByRoles(array $roles)
    {
        /** @var Role[] $roles */
        $users = [];
        if (count($roles)) foreach ($roles as $role) {
            if (!($role instanceof Role)) continue;
            foreach ($this->onlyActiveUsers ? $role->getActiveUsers() : $role->getUsers() as $user) {
                $users[] = $user;
            }
        }

        return $users;
    }

    public function getModelByPk($id)
    {
        return \mcms\user\models\User::findOne(['id' => $id]);
    }

    public function search(array $andFilterWhere, $isAndComparison = true, $limit = 10, $isActiveDataProvider = false, array $roles = [])
    {
        $query = \mcms\user\models\User::find();
        $query->limit($limit);

        $this->onlyActiveUsers && $query->andWhere(['status' => \mcms\user\models\User::STATUS_ACTIVE]);
        foreach ($andFilterWhere as $andFilterWhereItem) {
            $isAndComparison
                ? $query->andFilterWhere($andFilterWhereItem)
                : $query->orFilterWhere($andFilterWhereItem);
        }

        if ($roles) {
            $query->joinWith(['roles']);
            $query->andFilterWhere(['in', Role::tableName() . '.' . 'name', $roles]);
        }

        /*
         * Прячем юзеров, недоступных реселлеру
         */
        $notAvailableUserIds = !$this->ignoreNotAvailableUsers
            ? (new NotAvailableUserIds([
                'userId' => Yii::$app->user->id,
                'skipCurrentUser' => $this->skipCurrentUser,
            ]))->getResult()
            : [];

        if (count($notAvailableUserIds) > 0) {
            $query->andFilterWhere(['not in', 'id', $notAvailableUserIds]);
        }

        $this->setDataProvider(new ActiveDataProvider([
            'query' => $query
        ]));

        if ($isActiveDataProvider) {
            $this->setResultTypeDataProvider();
        }

        return $this->getResult();
    }

    public function join(Query &$query)
    {
        $query
            ->setRightTable('users')
            ->setRightTableColumn('id')
            ->join();
    }

    public function hasOne(ActiveRecord $model, $column)
    {
        return $this->hasOneRelation($model, \mcms\user\models\User::class, ['id' => $column]);
    }
}