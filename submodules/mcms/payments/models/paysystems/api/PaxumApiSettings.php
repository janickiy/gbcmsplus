<?php
namespace mcms\payments\models\paysystems\api;
use mcms\common\traits\Translate;
use rgk\payprocess\components\handlers\PaxumPayHandler;

/**
 * @see PaxumPayHandler
 */
class PaxumApiSettings extends BaseApiSettings
{
  use Translate;

  const LANG_PREFIX = 'payments.payment-systems-api.';

  /** @var string */
  public $email;
  /** @var string */
  public $secretCode;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['email', 'secretCode'], 'string'],
      [['email', 'secretCode'], 'required'],
      [['email'], 'email'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'email',
      'secretCode',
    ]);
  }

  /**
   * @inheritdoc
   */
  public function attributeHints()
  {
    return [
      'secretCode' => \Yii::_t('payments.payment-systems-api.get-credentials-paxum'),
    ];
  }

  /**
   * Поддерживаемые платежные системы и валюты
   * @return string[]
   */
  public function getAvailableRecipients()
  {
    return [
      'paxum',
    ];
  }
}