<?php

namespace mcms\user\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\user\models\ProfileNotificationForm;
use mcms\user\models\UserParam;
use mcms\common\module\api\join\Query;

class UserParams extends ApiResult
{
    protected $userId;

    function init($params = [])
    {
        $this->userId = ArrayHelper::getValue($params, 'userId', null);
        if (!$this->userId) $this->addError('userId is not set');
    }

    public function getResult()
    {
        if (!$this->userId) return false;
        return ArrayHelper::toArray(UserParam::getByUserId($this->userId));
    }

    public function getWebmasterType()
    {
        return UserParam::PARTNER_TYPE_WEBMASTER;
    }

    public function getArbitraryType()
    {
        return UserParam::PARTNER_TYPE_ARBITRARY;
    }

    public function getProfileNotificationForm()
    {
        $model = ProfileNotificationForm::getByUserId($this->userId);
        $model->setScenario($model::SCENARIO_EDIT_NOTIFICATION_SETTINGS);
        return $model;
    }

    public function join(Query &$query)
    {
        $query
            ->setRightTable('user_params')
            ->setRightTableColumn('user_id')
            ->join();
    }

    public function hidePromoModal()
    {
        /** @var UserParam $model */
        $model = UserParam::getByUserId($this->userId);
        $model->show_promo_modal = false;
        $model->setScenario(UserParam::SCENARIO_EDIT_TOGGLE_PROMO_MODAL);
        return $model->save();
    }
}