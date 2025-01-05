<?php
namespace mcms\payments\models\paysystems\api;

use Yii;
use yii\helpers\Url;

/**
 * Автовыплаты вебмани. Настройки для подключения к апи
 * @package mcms\payments\models\paysystems\api
 */
class WebmoneyApiSettings extends BaseApiSettings
{
  public $wmid;
  public $wallet;
  public $WMKwmFile;
  public $WMKwmFilePassword;
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
      [['wmid', 'wallet', 'WMKwmFile', 'WMKwmFilePassword'], 'required'],
      [['WMCapitallerId'], 'safe'],
    ];
  }

  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'wmid' => 'WMID',
      'wallet' => Yii::_t('payments.payment-systems-api.attribute-pursesrc'),
      'WMKwmFile' => Yii::_t('payments.payment-systems-api.attribute-WMKwmFile'),
      'WMKwmFilePassword' => Yii::_t('payments.payment-systems-api.attribute-WMKwmFilePassword'),
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
      'deleteUrl' => Url::to(['/payments/payment-systems-api/file-delete', 'code' => 'webmoney']),
    ];
    return [
      'WMKwmFile' => $paysystemForm->fileInput('WMKwmFile', $options, $widgetOptions),
    ];
  }
}