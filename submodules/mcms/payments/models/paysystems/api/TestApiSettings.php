<?php
namespace mcms\payments\models\paysystems\api;

// TODO Удалить, когда будут добавлены настоящие платежные системы
use yii\helpers\Url;

class TestApiSettings extends BaseApiSettings
{
  public $email;
  public $key;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['email', 'key'], 'required']
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

  public function getAdminCustomFields($form, $options = [])
  {
    return [
      'key' => $this->getForm($form)->fileInput('key', $options, [
        'uploadUrl' => Url::to(['/payments/payment-systems-api/file-upload']),
        'deleteUrl' => Url::to(['/payments/payment-systems-api/file-delete', 'code' => 'example']),
      ]),
    ];
  }
}