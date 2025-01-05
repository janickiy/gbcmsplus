<?php
namespace mcms\payments\models\paysystems;

use mcms\common\traits\Translate;
use mcms\payments\components\RemoteWalletBalances;
use mcms\payments\lib\payprocess\components\PayoutServiceProxy;
use mcms\payments\models\paysystems\api\BaseApiSettings;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;

/**
 * API платежной системы
 *
 * @property integer $id
 * @property string $name Название
 * @property string $code Код (в формате payment-system)
 * * @property string $currency валюта
 * @property string $settings JSON-массив настроек для обращения к API
 */
class PaySystemApi extends ActiveRecord
{
  use Translate;

  const LANG_PREFIX = 'payments.payment-systems-api.';
  const API_CLASSES_NAMESPACE = 'mcms\payments\models\paysystems\api\\';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'payment_systems_api';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['name', 'code', 'currency'], 'required'],
      [['settings'], 'string'],
      [['name'], 'string', 'max' => 64],
      [['code'], 'string', 'max' => 16],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels([
      'id',
      'name',
      'code',
      'settings',
      'currency',
      'balance',
    ]);
  }

  /**
   * Фильтрация полей
   * @see \rgk\utils\helpers\FilterHelper
   */
  public function filterRules()
  {
    return [
      'settings' => false,
    ];
  }

  public function afterSave($insert, $changedAttributes)
  {
    RemoteWalletBalances::invalidateCache();
    parent::afterSave($insert, $changedAttributes);
  }


  /**
   * Доступно ли API для использования
   * @return bool
   */
  public function isActive()
  {
    return $this->isValidSettings();
  }

  /**
   * API платежных систем доступные для выполнения выплаты на указанную платежную систему
   * @param string $code wallets.code
   * @param string $currency
   * @return PaySystemApi[]
   */
  public static function getAvailableApiByRecipient($code, $currency)
  {
    $paySystemsApi = [];
    /** @var PaySystemApi $paySystem */
    foreach (static::find()->where(['currency' => $currency])->each() as $paySystem) {
      $paysystems = $paySystem->getSettingsObject()->getAvailableRecipients();

      if (!in_array($code, $paysystems)) {
        continue;
      }

      $paySystemsApi[] = $paySystem;
    }

    return $paySystemsApi;
  }

  /**
   * @see getAvailableApiByRecipient()
   * @param $code
   * @param $currency
   * @return \string[]
   */
  public static function getAvailableApiByRecipientAsItems($code, $currency)
  {
    return ArrayHelper::map(static::getAvailableApiByRecipient($code, $currency), 'id', 'name');
  }

  /**
   * Настройки API в виде массива
   * @return array
   */
  public function getSettingsAsArray()
  {
    return Json::decode($this->settings);
  }

  /**
   * Класс с параметрами ПС для работы с API
   * @return string|BaseApiSettings
   */
  public function getSettingsClass()
  {
    return static::API_CLASSES_NAMESPACE . Inflector::camelize($this->code) . 'ApiSettings';
  }

  /**
   * Объект заполненный параметрами ПС для работы с API
   * @return bool|BaseApiSettings
   * @throws \Exception
   */
  public function getSettingsObject()
  {
    $class = $this->getSettingsClass();
    if (!class_exists($class)) {
      throw new \Exception('Модель API платежной системы ' . $this->name . ' не найдено (' . $class . ')');
    }
    /** @var BaseApiSettings $object */
    $object = new $class;
    $object->setAttributes($this->getSettingsAsArray());
    $object->setPaysystemApi($this);

    return $object;
  }

  /**
   * Валидна ли конфигурация платежной системы
   * @return bool
   */
  public function isValidSettings()
  {
    return $this->getSettingsObject()->validate();
  }

  public function getBalance()
  {
    return PayoutServiceProxy::getBalance($this->id);
  }

  /**
   * @return bool
   */
  public function isBalanceApiAvailable()
  {
    return in_array($this->code, [
      'yandex-money',
      'paypal',
      'webmoney',
      'wmlight',
      'paxum',
      'epayments',
      'capitalist',
      'usdt'
    ], true);
  }

  /**
   * Установить настройки ПС
   * @param BaseApiSettings $settings
   */
  public function setSettings(BaseApiSettings $settings)
  {
    $this->settings = (string) $settings;
  }
}
