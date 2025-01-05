<?php

namespace mcms\user\components;

use mcms\user\models\User;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Json;

/**
 * Определение потребности в отображении капчи после n-ного количества попыток входа или сброса пароля
 * TRICKY $user специально каждый раз передается в методы, а не в конструктор, так как компонент используется в моделях,
 * где $user может отсутствовать например до вызова метода Model::load(). Короче сделано для удобства
 */
class CaptchaCheck extends BaseObject
{
    /**
     * Увеличить количество попыток
     * @param User $user
     */
    public function incrementAttempts(User $user = null)
    {
        if (!$user) return;

        $user->login_attempts++;
        if (!$user->save()) {
            Yii::error(
                'Не удалось увеличить счетчик попыток авторизации. User: ' . Json::encode($user->attributes),
                __METHOD__
            );
        }
    }

    /**
     * Сбросить количестов попыток
     * @param User $user
     */
    public function resetAttempts(User $user = null)
    {
        if (!$user) return;

        $user->login_attempts = null;
        if (!$user->save()) {
            Yii::error(
                'Не удалось сбросить счетчик попыток авторизации. User: ' . Json::encode($user->attributes),
                __METHOD__
            );
        }
    }

    /**
     * Получить количество попыток
     * @param User $user
     * @return int|null
     */
    public function getAttempts(User $user = null)
    {
        if (!$user) return null;

        return (int)$user->login_attempts;
    }

    /**
     * Попытки исчерпаны
     * @param User $user
     * @return bool
     */
    public function isCaptchaRequired(User $user = null)
    {
        /** @var \mcms\user\Module $module */
        $module = Yii::$app->getModule('users');
        $maxAttempts = $module->captchaShowAfterFailLogin();

        if ($maxAttempts == 0) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $attempts = static::getAttempts($user);
        if (!$attempts || !$maxAttempts) {
            return false;
        }

        return $attempts >= $maxAttempts;
    }
}