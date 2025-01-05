<?php

namespace mcms\common\auth;

use Yii;
use yii\base\InvalidConfigException;
use yii\filters\auth\AuthMethod;

/**
 * Class TokenAuth
 * @package mcms\common\auth
 */
class TokenAuth extends AuthMethod
{

    /**
     * @var string the parameter name for passing the access token
     */
    public $tokenParam = 'hash';

    /**
     * @var string the parameter time
     */
    public $timeParam = 'time';

    public $apiParams;

    public function init()
    {
        parent::init();

        if (!isset(Yii::$app->params['api']) && !is_array(Yii::$app->params['api']))
            throw new InvalidConfigException('API params required');

        $this->apiParams = Yii::$app->params['api'];

        if (!isset($this->apiParams['secretKey']))
            throw new InvalidConfigException('Life time param required');

        if (!isset($this->apiParams['lifeTime']))
            throw new InvalidConfigException('Life time param required');

    }


    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {

        $accessToken = $request->get($this->tokenParam);
        $time = (int)$request->get($this->timeParam);


        if ($accessToken && $time && (time() - $time) >= $this->apiParams['lifeTime']) {
            $this->handleFailure($response);
        }

        if (md5($this->apiParams['secretKey'] . $time) !== $accessToken) {
            $this->handleFailure($response);
        }

        return true;
    }

}