<?php

namespace mcms\common\mgmp;

use mcms\payments\Module;
use Yii;
use yii\filters\auth\AuthMethod;

/**
 * Class TokenAuth
 * @package mcms\common\mgmp
 */
class TokenAuth extends AuthMethod
{

    /** пока храним тут, потом можно вынести в настройки */
    const LIFETIME = 3600 * 24;

    /**
     * @var string the parameter name for passing the access token
     */
    public $tokenParam = 'access_token';

    /**
     * @var string the parameter time
     */
    public $timeParam = 'time';

    /**
     * @param $user
     * @param $request
     * @param $response
     * @return true
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function authenticate($user, $request, $response)
    {
        $accessToken = $request->get($this->tokenParam);
        $time = (int)$request->get($this->timeParam);


        if ($accessToken && $time && (time() - $time) >= self::LIFETIME) {
            $this->handleFailure($response);
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('payments');
        $secretKey = $module->getMgmpSecretKey();
        if (md5($secretKey . $time) !== $accessToken) {
            $this->handleFailure($response);
        }

        return true;
    }
}