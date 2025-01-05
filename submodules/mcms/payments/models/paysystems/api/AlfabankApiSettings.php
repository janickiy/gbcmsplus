<?php
namespace mcms\payments\models\paysystems\api;

use mcms\common\helpers\Html;
use Yii;
use yii\helpers\Url;

class AlfabankApiSettings extends BaseApiSettings
{
  public $card_number;
  public $exp_date;
  public $cvv;
  public $login;
  public $password;
//  public $request_token;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['login', 'password', 'card_number', 'exp_date', 'cvv'], 'required']
    ];
  }

  public function validateAccessToken()
  {
    // TODO запилить валидацию акцес токена, чтобы было видно если он не проходит
    // тут возможно и не стоит этого делать, так как есть еще refresh_token
  }

  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'cvv' => 'CVV',
      'card_number' => Yii::_t('payments.payment-systems-api.attribute-card'),
      'exp_date' => Yii::_t('payments.payment-systems-api.attribute-exp_date'),
    ];
  }

  /**
   * Поддерживаемые платежные системы и валюты
   * @return string[]
   */
  public function getAvailableRecipients()
  {
    return [
      'card',
    ];
  }

  /**
   * @inheritdoc
   */
  public function getAdminCustomFields($form, $options = [])
  {
    $paysystemForm = $this->getForm($form);

    return [
      'password' => $paysystemForm->passwordInput('password', $options, []),
      'request_token' => Html::a('Get token', ['/payments/payment-systems-auth/alfabank'], ['target' => '_blank', 'data-pjax' => 0]),
    ];
  }
}