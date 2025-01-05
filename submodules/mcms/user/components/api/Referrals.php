<?php

namespace mcms\user\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\user\models\User as UserModel;
use mcms\user\models\search\User as UserSearch;
use yii\db\Query;

class Referrals extends ApiResult
{
    private $_searchModel;
    private $_partnerReferralSearch;

    public function init($params = array())
    {
        if (ArrayHelper::getValue($params, 'partnerSearch', null) === true) {
            $this->_partnerReferralSearch = true;
        }
        return $this;
    }

    /**
     * Получаем список с рефералами пользователя
     * @param int $params
     * @param int $userId
     * @return \yii\data\ActiveDataProvider
     */
    public function searchReferrals($params, $userId)
    {
        $this->_searchModel = new UserSearch([
            'scenario' => $this->_partnerReferralSearch ? UserSearch::SCENARIO_PARTNER_REFERRAL_SEARCH : UserSearch::SCENARIO_DEFAULT
        ]);
        return $this->_searchModel->searchReferrals($params, $userId);
    }

    /**
     * Получение модели поиска
     * @return \mcms\user\models\search\User
     */
    public function getSearchModel()
    {
        return $this->_searchModel;
    }

    /**
     * Джойним таблицу с рефералами по юзеру
     * @param \yii\db\Query $query
     * @param string $tableName
     * @param string $fieldName
     * @param integer $userId
     * @param string $joinType
     * @return Query
     */
    public function joinByUser(Query $query, $tableName, $fieldName, $userId, $joinType = 'INNER JOIN')
    {
        $query->join(
            $joinType, UserModel::TABLE_USERS_REFERRALS, sprintf(
                '%s.%s=%s.%s', UserModel::TABLE_USERS_REFERRALS, UserModel::FIELD_REFERRAL_ID, $tableName, $fieldName
            )
        );

        $query->andWhere(['=', UserModel::TABLE_USERS_REFERRALS . '.' . UserModel::FIELD_USER_ID, $userId]);
        return $query;
    }

    public function getUserTableName()
    {
        return UserModel::TABLE_USERS_REFERRALS;
    }

    /**
     * Получение количества рефералов для пользователя
     * @param type $userId
     * @return type
     */
    public function getReferralCount($userId)
    {
        /**
         * @todo Remove me to model
         */
        $userIdentity = UserModel::findOne(['id' => $userId]);
        return $userIdentity ? $userIdentity->getReferrals()->count() : 0;
    }

}
