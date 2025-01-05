<?php

namespace mcms\user\components\api;

use mcms\user\Module;
use Yii;
use yii\helpers\Url;
use mcms\common\module\api\ApiResult;
use mcms\user\components\ReferralDecoder;

class UserLink extends ApiResult
{
    function init($params = [])
    {
        return;
    }

    /**
     * Генерирует ссылку на профиль юзера
     * @param integer $userId
     * @param string $content
     * @return string
     */
    public function buildProfileLink($userId)
    {
        return ['/' . Yii::$app->getModule('users')->id . '/users/view/', 'id' => $userId];
    }

    /**
     * Генерирует ссылку для привлечения рефералов
     * @param integer $userId
     * @return string
     */
    public function buildReferralLink($userId)
    {
        return Yii::$app->getUrlManager()->getHostInfo() . "/refid/" . ReferralDecoder::encode($userId) . '/';
    }

    /**
     * Генерирует ссылку для редактирования профиля пользователя.
     * @return string
     */
    public function buildProfileEditLink()
    {
        if (Yii::$app->user->identity->hasRole(Module::PARTNER_ROLE)) {
            return Url::to('/' . Yii::$app->getModule('partners')->id . '/profile/index/');
        }
        return ["/" . Yii::$app->getModule('users')->id . "/users/profile/"];
    }

    public function getUsersListLinkParams()
    {
        return ['/' . Yii::$app->getModule('users')->id . '/users/list/'];
    }

    public function getUserUpdateLinkParams($userId)
    {
        return ['/' . Yii::$app->getModule('users')->id . '/users/update/', 'id' => $userId];
    }
}