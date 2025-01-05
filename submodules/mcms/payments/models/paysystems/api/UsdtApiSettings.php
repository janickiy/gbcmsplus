<?php

namespace mcms\payments\models\paysystems\api;

use mcms\common\traits\Translate;
use yii\helpers\Url;

class UsdtApiSettings extends BaseApiSettings
{
  use Translate;
  
  const LANG_PREFIX = 'payments.payment-systems-api.';
  
  /** @var string */
  public $username;
  /** @var string */
  public $password;
  /**
   * @var string файл сертификата
   */
  public $certificateFile;
  /**
   * @var string ключ файла сертификата
   */
  public $certificateKey;
  
  
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['username', 'password', 'certificateKey'], 'filter', 'filter' => 'strip_tags'],
      [['username', 'password', 'certificateKey'], 'filter', 'filter' => 'trim'],
      [['username', 'password', 'certificateKey'], 'string'],
      [['username', 'password', 'certificateFile'], 'required'],
    
    ];
  }
  
  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'username',
      'password',
      'certificateKey',
      'certificateFile'
    ]);
  }
  
  /**
   * @inheritdoc
   */
  public function getAdminCustomFields($form, $options = [])
  {
    $paysystemForm = $this->getForm($form);
    
    $widgetOptions = [
      'uploadUrl' => Url::to(['/payments/payment-systems-api/file-upload']),
      'deleteUrl' => Url::to(['/payments/payment-systems-api/file-delete', 'code' => 'usdt']),
    ];
    return [
      'certificateFile' => $paysystemForm->fileInput('certificateFile', $options, $widgetOptions),
    ];
  }
  
  /**
   * @inheritDoc
   */
  public function getAvailableRecipients()
  {
    return [
      'usdt',
    ];
  }
}