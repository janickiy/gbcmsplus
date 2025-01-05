<?php

namespace mcms\payments\components\widgets;

use mcms\payments\models\UserPaymentSetting;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

class PartnerSettings extends Widget {

  const URL = '/payments/users/';

  /**
   * @var bool Отображать как содержимое модального окна (только для options[getPartial])
   */
  public $isModal = false;
  /**
   * @var array
   */
  public $options;

  /**
   * @inheritDoc
   */
  public function init()
  {
    parent::init();
    ob_start();
  }

  /**
   * @inheritDoc
   */
  public function run()
  {
    if (!$userId = ArrayHelper::getValue($this->options, 'userId')) return null;

    if (!$userModule = Yii::$app->getModule('users')) return null;
    if (!$promoModule = Yii::$app->getModule('promo')) return null;

    $model = UserPaymentSetting::fetch($this->options['userId']);
    $model->setScenario(ArrayHelper::getValue($this->options, 'scenario', $model::SCENARIO_PARTNER_UPDATE));

    if ($currency = ArrayHelper::getValue($this->options, 'currency')) {
      $model->setAttribute('currency', $currency);
    }

    $canChangeWallet = $model->canChangeWallet();
    $canChangeCurrency = $model->canChangeCurrency($currency);

    if (ArrayHelper::getValue($this->options, 'getPartial') === true) {
      return $this->render('_partner_settings_form', [
        'model' => $model,
        'modal' => $this->isModal,
        'canChangeWallet' => $canChangeWallet,
      ]);
    }

    return $this->render('partner_settings', [
      'model' => $model,
      'canChangeWallet' => $canChangeWallet,
      'canChangeCurrency' => $canChangeCurrency,
      'canChangeCurrencyError' => $canChangeCurrency ? null : $model->canChangeCurrencyLastError(),
    ]);
  }


}