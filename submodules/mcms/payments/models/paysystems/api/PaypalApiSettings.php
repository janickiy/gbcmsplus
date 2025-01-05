<?php
namespace mcms\payments\models\paysystems\api;

use mcms\common\traits\Translate;

/**
 * TRICKY Правильно это или нет, но для песочницы использовались clientId и clientSecret из примера SDK
 * ClientID: AYSq3RDGsmBLJE-otTkBtM-jBRd1TCQwFf9RGfwddNXWz0uFU9ztymylOhRS
 * ClientSecret: EGnHDxD_qRPdaLdZz8iCr8N7_MzF-YHPTkjs6NKYQvQSBngp4PTTVWkPZRbL
 * @see https://github.com/paypal/PayPal-PHP-SDK/wiki/Going-Live Получение параметров приложения clientId и clientSecret для продакшена
 */
class PaypalApiSettings extends BaseApiSettings
{
  use Translate;

  const LANG_PREFIX = 'payments.payment-systems-api.';

  /** @var string */
  public $clientId;
  /** @var string */
  public $clientSecret;
  /** @var string */
  public $userName;
  /** @var string */
  public $password;
  /** @var string */
  public $signature;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['clientId', 'clientSecret', 'userName', 'password', 'signature'], 'string'],
      [['clientId', 'clientSecret'], 'required'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'clientId',
      'clientSecret',
      'userName',
      'password',
      'signature',
    ]);
  }

  /**
   * @inheritdoc
   */
  public function attributeHints()
  {
    return [
      'clientSecret' => \Yii::_t('payments.payment-systems-api.get-credentials-paypal', ['link' => 'https://developer.paypal.com/docs/integration/direct/make-your-first-call/#create-a-paypal-app']),
      'signature' => \Yii::_t('payments.payment-systems-api.get-api-credentials-paypal', ['link' => 'https://developer.paypal.com/docs/classic/api/apiCredentials/#create-and-manage-certificate-credentials']),
    ];
  }

  /**
   * Поддерживаемые платежные системы и валюты
   * @return string[]
   */
  public function getAvailableRecipients()
  {
    return [
      'paypal',
    ];
  }
}