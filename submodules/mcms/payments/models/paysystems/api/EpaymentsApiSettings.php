<?php
namespace mcms\payments\models\paysystems\api;

use mcms\common\traits\Translate;
use yii\helpers\Url;

class EpaymentsApiSettings extends BaseApiSettings
{
  use Translate;

  const LANG_PREFIX = 'payments.payment-systems-api.';

  public $partnerId;
  public $partnerSecret;
  public $sourcePurse;
  public $payPass;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['partnerId', 'partnerSecret', 'sourcePurse', 'payPass'], 'required']
    ];
  }

  /**
   * Поддерживаемые платежные системы и валюты
   * @return string[]
   */
  public function getAvailableRecipients()
  {
    return [
      'epayments',
      'yandex-money',
      'card',
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels([
      'partnerId',
      'partnerSecret',
      'sourcePurse',
      'payPass',
    ]);
  }
}