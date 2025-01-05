<?php

namespace mcms\user\components\widgets;

use mcms\common\helpers\ArrayHelper;
use mcms\user\models\SignupForm;
use mcms\user\models\SignupFormSecond;
use yii\base\Widget;
use Yii;

class SignupFormWidget extends Widget
{

    public $landing;

    public $options;

    public function run()
    {
        /* @var \mcms\user\Module $module */
        $module = Yii::$app->getModule('users');
        $secondForm = ArrayHelper::getValue($this->options, 'secondForm', false);

        // TRICKY: SignupFormSecond используется для того, чтобы поместить 2 формы регистрации на страницу
        /** @var SignupForm $model */
        $model = Yii::createObject($secondForm ? SignupFormSecond::class : SignupForm::class);
        $model->language = $module->languageUser();
        $model->currency = $module->currencyUser();

        $currencyList = Yii::$app->getModule('promo')->api('mainCurrencies', ['availablesOnly' => true])->setMapParams(['code', 'name'])->getMap();

        if ($module->isCaptchaEnabledRegistration()) {
            $model->isRecaptchaValidator = true;
        }

        return $this->render('signup', [
            'model' => $model,
            'premoderate' => $module->isRegistrationTypeByHand(),
            'currencyList' => $currencyList,
            'options' => $this->options,
            'formId' => $secondForm ? 'signup-form-second' : 'signup-form'
        ]);
    }
}
