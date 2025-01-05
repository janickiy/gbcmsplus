<?php
namespace mcms\payments\models\paysystems\api;

use Yii;
use yii\helpers\Url;

class YandexMoneyApiSettings extends BaseApiSettings
{
  public $wallet;
  public $clientId;
  public $clientSecret;
  public $redirectURI;
  public $scope = 'account-info operation-history operation-details payment-p2p.limit(1,100000)';
  public $accessToken;



  public function init()
  {
    $this->redirectURI = Url::to('@web/payments/payment-systems-auth/yandex-money/', true);
    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['clientId', 'clientSecret', 'scope', 'wallet', 'scope', 'redirectURI'], 'required'],
      [['accessToken'], 'string']
    ];
  }


  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'wallet' => Yii::_t('payments.payment-systems-api.attribute-yandex-money-wallet'),
      'clientId' => Yii::_t('payments.payment-systems-api.attribute-yandex-money-client-id'),
      'clientSecret' => Yii::_t('payments.payment-systems-api.attribute-yandex-money-client-secret'),
      'accessToken' => Yii::_t('payments.payment-systems-api.attribute-yandex-money-access-token'),
      'scope' => Yii::_t('payments.payment-systems-api.attribute-yandex-money-scope'),
      'redirectURI' => Yii::_t('payments.payment-systems-api.attribute-yandex-money-redirect-uri'),
    ];
  }

  /**
   * Поддерживаемые платежные системы и валюты
   * @return string[]
   */
  public function getAvailableRecipients()
  {
    return [
      'yandex-money',
    ];
  }

  public function getAccessTokenUrl()
  {
    return Url::toRoute('payment-systems-auth/yandex-money');
  }

}