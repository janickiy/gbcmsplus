<?php
namespace mcms\payments\models\paysystems\api;

use Yii;
use yii\helpers\Url;

/**
 * Автовыплаты вебмани light. Настройки для подключения к апи
 * @package mcms\payments\models\paysystems\api
 */
class WmlightApiSettings extends BaseApiSettings
{
  /**
   * @var string номер кошелька с которого выполняется перевод (отправитель)
   */
  public $wallet;
  /**
   * @var string файл сертификата
   */
  public $certificateFile;
  /**
   * @var string ключ файла сертификата
   */
  public $certificateKey;
  /**
   * @var string пароль для ключа
   * (пока не используется - надо обновить либу baibaratsky/php-webmoney)
   */
//  public $password;
  /**
   * @var string WM Capitaller WMID (для получения баланса кошельков)
   */
  public $WMCapitallerId;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['wallet', 'certificateFile', 'certificateKey'], 'required'],
      [['WMCapitallerId',], 'safe'],
    ];
  }

  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'wallet' => Yii::_t('payments.payment-systems-api.attribute-pursesrc'),
      'certificateFile' => Yii::_t('payments.payment-systems-api.attribute-certificateFile'),
      'certificateKey' => Yii::_t('payments.payment-systems-api.attribute-certificateKey'),
      'password' => Yii::_t('payments.payment-systems-api.attribute-certificatePassword'),
      'WMCapitallerId' => Yii::_t('payments.payment-systems-api.attribute-WMCapitallerId'),
    ];
  }

  /**
   * Поддерживаемые платежные системы и валюты
   * @return string[]
   */
  public function getAvailableRecipients()
  {
    return [
      'webmoney',
    ];
  }

  /**
   * @inheritdoc
   */
  public function getAdminCustomFields($form, $options = [])
  {
    $paysystemForm = $this->getForm($form);

    $widgetOptions = [
      'uploadUrl' => Url::to(['/payments/payment-systems-api/file-upload']),
      'deleteUrl' => Url::to(['/payments/payment-systems-api/file-delete', 'code' => 'wmlight']),
    ];
    return [
      'certificateFile' => $paysystemForm->fileInput('certificateFile', $options, $widgetOptions),
      'certificateKey' => $paysystemForm->fileInput('certificateKey', $options, $widgetOptions),
    ];
  }
}