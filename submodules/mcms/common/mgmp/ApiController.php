<?php

namespace mcms\common\mgmp;

use mcms\payments\lib\mgmp\TokenAuth;
use Yii;

/**
 * Class ApiController
 * @package mcms\common\mgmp
 */
class ApiController extends \yii\rest\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $user = Yii::$app->getUser();
        $user->enableSession = false;

        return [
            'authenticator' => [
                'class' => TokenAuth::class,
                'user' => $user,
            ]
        ];
    }
}