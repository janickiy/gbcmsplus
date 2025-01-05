<?php

namespace mcms\user\controllers\apiv1;

use mcms\payments\components\UserBalance;
use Yii;
use Exception;
use mcms\common\controller\ApiController;
use mcms\user\models\User;

/**
 * Class UserController
 */
class UserController extends ApiController
{

    public function actionAuth()
    {
        if (!Yii::$app->user->isGuest) {
            throw new Exception('already logged in');
        }

        $user = User::findByEmail(Yii::$app->request->getBodyParam('email'));
        if (!$user || !$user->validatePassword(Yii::$app->request->getBodyParam('pass'))) {
            return false;
        }

        list($access_token, $token_expire) = $user->getNewAccessToken();

        return ['access_token' => $access_token, 'token_expire' => $token_expire];
    }


    public function actionIndex()
    {
        /* @var User $user */
        $user = Yii::$app->user->getIdentity();
        return [
            'username' => $user->username
        ];
    }

    public function actionBalance()
    {

        /* @var UserBalance $userBalance */
        $userBalance = Yii::$app->getModule('payments')
            ->api('userBalance', ['userId' => Yii::$app->user->id])
            ->getResult();

        return [
            'currency' => $userBalance->currency,
            'main' => (float)round($userBalance->getMain(), 2),
            'today' => (float)round($userBalance->getTodayProfit(), 2),
            'hold' => (float)round($userBalance->getHold(), 2),
        ];
    }
}
