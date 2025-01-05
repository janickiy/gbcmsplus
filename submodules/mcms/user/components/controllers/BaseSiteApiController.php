<?php

namespace mcms\user\components\controllers;

use Yii;
use yii\web\Controller;

class BaseSiteApiController extends Controller
{
    public function beforeAction($action)
    {
        if ($this->module->isRegistrationWithReferrals()
            && ($refId = Yii::$app->request->get('refId')) !== null) {
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'refId',
                'value' => $refId,
                'expire' => time() + 86400 * 2,
            ]));
        }
        return parent::beforeAction($action);
    }
}