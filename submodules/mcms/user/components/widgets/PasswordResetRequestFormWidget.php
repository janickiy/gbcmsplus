<?php

namespace mcms\user\components\widgets;

use mcms\user\models\PasswordResetRequestForm;
use yii\base\Widget;
use Yii;

class PasswordResetRequestFormWidget extends Widget
{
    const JS_API_URL = '//www.google.com/recaptcha/api.js';

    public $landing;

    public $options;

    public function run()
    {
        $model = new PasswordResetRequestForm();

        if (!$model->shouldUseCaptcha()) {
            $view = $this->view;
            $view->registerJsFile(
                self::JS_API_URL . '?hl=' . $this->getLanguageSuffix(),
                ['position' => $view::POS_HEAD, 'async' => true, 'defer' => true]
            );
        }

        return $this->render('requestPasswordReset', [
            'model' => $model,
            'options' => $this->options
        ]);
    }

    protected function getLanguageSuffix()
    {
        $currentAppLanguage = Yii::$app->language;
        $langsExceptions = ['zh-CN', 'zh-TW', 'zh-TW'];

        if (strpos($currentAppLanguage, '-') === false) {
            return $currentAppLanguage;
        }

        if (in_array($currentAppLanguage, $langsExceptions)) {
            return $currentAppLanguage;
        } else {
            return substr($currentAppLanguage, 0, strpos($currentAppLanguage, '-'));
        }
    }
}