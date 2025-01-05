<?php

namespace mcms\payments\models\export;

use mcms\common\traits\Translate;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\models\wallet\Wallet;

/**
 * UserPaymentExportForm занимается работой с формой для экспорта
 * @property-read string $prevLink
 */
class UserPaymentExportForm extends \yii\db\ActiveRecord
{
  use Translate;

  const LANG_PREFIX = 'payments.export.';

  protected static $statusesList;
  protected static $walletTypesList;
  private $_prevLink;
  public $status_ids;
  public $wallet_ids;
  public $ids;

  /**
   * Получение списка статусов
   * @return type
   */
  public static function getStatusesList()
  {
    return UserPayment::getPayableStatuses();
  }

  /**
   * Получение списка кошельков
   * @return type
   */
  public static function getWalletTypesList()
  {
    return static::$walletTypesList ? : (static::$walletTypesList = Wallet::getWallets());
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{user_payments}}';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['wallet_ids', 'default', 'value' => array_keys(static::getWalletTypesList())],
      ['wallet_ids', 'each', 'rule' => ['integer']],
      ['wallet_ids', 'each', 'rule' => ['in', 'range' => array_keys(static::getWalletTypesList())]],
      ['status_ids', 'default', 'value' => array_keys(static::getStatusesList())],
      ['status_ids', 'each', 'rule' => ['integer']],
      ['status_ids', 'each', 'rule' => ['in', 'range' => array_keys(static::getStatusesList())]],
      [['status_ids', 'wallet_ids'], 'safe']
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return [
      self::SCENARIO_DEFAULT => ['status_ids', 'wallet_ids'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'status_ids' => self::translate('attribute-status-ids'),
      'wallet_ids' => self::translate('attribute-wallet-ids'),
    ];
  }

  /**
   * Проверка, есть ли подходящие объекты на экспорт
   */
  public function checkExport()
  {
    return UserPayment::find()
      ->joinWith('userPaymentSetting')
      ->andFilterWhere([UserPayment::tableName() . '.status' => $this->status_ids])
      ->andFilterWhere(['not in', UserPaymentSetting::tableName() . '.user_id', UserPaymentExport::getNotAvailableUserIds()])
        ->andFilterWhere([UserPayment::tableName() . '.wallet_type' => $this->wallet_ids])
        ->limit(1)
        ->count() > 0;
  }

  /**
   * Подготовка значений по умолчанию для вывода формы
   */
  public function prepareDefaultValues()
  {
    $this->status_ids = array_keys(static::getStatusesList());
    $this->wallet_ids = array_keys(static::getWalletTypesList());
  }

  /**
   * Получение ссылки на существующий архив
   * @return type
   */
  public function getPrevLink()
  {
    return $this->_prevLink ? : ($this->_prevLink = (new UserPaymentExport)->getPrevLink());
  }
}