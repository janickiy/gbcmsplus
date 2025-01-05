<?php

namespace mcms\user\components\widgets;

use mcms\user\models\ResetPasswordForm;
use yii\base\InvalidArgumentException;
use yii\base\Widget;
use Yii;

class ResetPasswordFormWidget extends Widget
{
    public $landing;

    public $options;

    public function run()
    {
        $token = Yii::$app->request->get('token');

        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);

    }
}