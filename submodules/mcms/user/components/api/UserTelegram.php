<?php

namespace mcms\user\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\user\models\UserParam;

/**
 * Class UserTelegram
 * @package mcms\user\components\api
 */
class UserTelegram extends ApiResult
{
    protected $userId;

    /**
     * @inheritdoc
     */
    function init($params = [])
    {
        $this->userId = ArrayHelper::getValue($params, 'userId', null);
    }

    /**
     * Установить telegram_id
     * @param integer $id
     * @return bool
     */
    public function setTelegramId($id)
    {
        if (!$this->userId) return false;
        /** @var UserParam $model */
        $model = UserParam::getByUserId($this->userId);
        $model->telegram_id = $id;
        return $model->save();
    }

    /**
     * Удалить telegram_id
     * @return bool
     */
    public function unsetTelegramId()
    {
        if (!$this->userId) return false;
        /** @var UserParam $model */
        $model = UserParam::getByUserId($this->userId);
        $model->telegram_id = null;
        return $model->save();
    }

    /**
     * Вернуть всех у кого заполнен telegram_id
     * @return static[]
     */
    public function getUsersWithTelegram()
    {
        return UserParam::find()->where(['is not', 'telegram_id', null])->all();
    }
}