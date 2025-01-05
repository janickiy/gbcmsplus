<?php
namespace mcms\payments\models\paysystems\api;

use Yii;
use yii\helpers\Url;

/**
 * Class WireibanApiSettings
 * @package mcms\payments\models\paysystems\api
 */
class WireibanApiSettings extends BaseApiSettings
{
  public $apiKey;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['apiKey'], 'required']
    ];
  }

  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'apiKey' => 'API key',
    ];
  }

  /**
   * Поддерживаемые платежные системы и валюты
   * @return string[]
   */
  public function getAvailableRecipients()
  {
    return [
    ];
  }
}