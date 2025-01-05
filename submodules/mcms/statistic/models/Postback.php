<?php

namespace mcms\statistic\models;

use mcms\common\helpers\ArrayHelper;
use mcms\statistic\components\queue\postbacks\Payload;
use mcms\user\models\User;
use Yii;
use yii\db\Query;
use yii\helpers\Json;

/**
 * This is the model class for table "postbacks".
 *
 * @property integer $id
 * @property integer $hit_id
 * @property integer $subscription_id
 * @property integer $subscription_rebill_id
 * @property integer $subscription_off_id
 * @property integer $sold_subscription_id
 * @property integer $onetime_subscription_id
 * @property integer $complain_id
 * @property integer $source_id
 * @property integer $status
 * @property integer $errors
 * @property string $status_code
 * @property string $url
 * @property string $data
 * @property integer $time
 * @property integer $last_time
 *
 */
class Postback extends \yii\db\ActiveRecord
{

  const TYPE_SUBSCRIPTION = 1;
  const TYPE_SUBSCRIPTION_REBILL = 2;
  const TYPE_SUBSCRIPTION_OFF = 3;
  const TYPE_SOLD_SUBSCRIPTION = 4;
  const TYPE_ONETIME_SUBSCRIPTION = 5;
  const TYPE_COMPLAIN = 6;

  const STATUS_OK = 1;
  const STATUS_ERROR = 0;

  const DUPLICATE = 1;
  const ORIGINAL = 0;

  public $hitId;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'postbacks';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['hit_id','subscription_id', 'subscription_rebill_id', 'subscription_off_id', 'sold_subscription_id', 'onetime_subscription_id', 'complain_id', 'hitId', 'source_id', 'status', 'errors', 'time', 'last_time', 'status_code'], 'integer'],
      [['data'], 'string'],
      [['url', 'time', 'last_time'], 'required'],
      [['url'], 'string', 'max' => 512],
      [['subscription_id'], 'unique'],
      [['subscription_rebill_id'], 'unique'],
      [['subscription_off_id'], 'unique'],
      [['sold_subscription_id'], 'unique'],
      [['onetime_subscription_id'], 'unique'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'status' => Yii::_t('statistic.postbacks.status'),
      'source_id' => Yii::_t('statistic.postbacks.source_id'),
      'errors' => Yii::_t('statistic.postbacks.errors'),
      'status_code' => Yii::_t('statistic.postbacks.status_code'),
      'url' => Yii::_t('statistic.postbacks.url'),
      'data' => Yii::_t('statistic.postbacks.data'),
      'time' => Yii::_t('statistic.postbacks.time'),
      'type' => Yii::_t('statistic.postbacks.type'),
      'transId' => Yii::_t('statistic.postbacks.trans_id'),
      'userId' => Yii::_t('statistic.postbacks.userId'),
      'last_time' => 'Last Time',
    ];
  }

  /**
   * @return int
   */
  public function getTransId()
  {
    if ($this->subscription_id) return $this->subscription_id;
    if ($this->subscription_rebill_id) return $this->subscription_rebill_id;
    if ($this->subscription_off_id) return $this->subscription_off_id;
    if ($this->sold_subscription_id) return $this->sold_subscription_id;
    if ($this->onetime_subscription_id) return $this->onetime_subscription_id;
    if ($this->complain_id) return $this->complain_id;
  }

  /**
   * @return int
   */
  public function getType()
  {
    if ($this->subscription_id) return self::TYPE_SUBSCRIPTION;
    if ($this->subscription_rebill_id) return self::TYPE_SUBSCRIPTION_REBILL;
    if ($this->subscription_off_id) return self::TYPE_SUBSCRIPTION_OFF;
    if ($this->sold_subscription_id) return self::TYPE_SOLD_SUBSCRIPTION;
    if ($this->onetime_subscription_id) return self::TYPE_ONETIME_SUBSCRIPTION;
    if ($this->complain_id) return self::TYPE_COMPLAIN;
  }

  /**
   * Название типа
   * @return string
   */
  public function getTypeLabel()
  {
    return ArrayHelper::getValue(self::getTypesList(), $this->type);
  }

  /**
   * Название статуса
   * @return string
   */
  public function getStatusLabel()
  {
    return ArrayHelper::getValue(self::getStatusList(), $this->status);
  }

  /**
   * Список типов
   * @return array
   */
  public static function getTypesList()
  {
    return [
      self::TYPE_SUBSCRIPTION => Yii::_t('statistic.postbacks.type-subscription'),
      self::TYPE_SUBSCRIPTION_REBILL => Yii::_t('statistic.postbacks.type-subscription_rebill'),
      self::TYPE_SUBSCRIPTION_OFF => Yii::_t('statistic.postbacks.type-subscription_off'),
      self::TYPE_SOLD_SUBSCRIPTION => Yii::_t('statistic.postbacks.type-sold_subscription'),
      self::TYPE_ONETIME_SUBSCRIPTION => Yii::_t('statistic.postbacks.type-onetime_subscription'),
      self::TYPE_COMPLAIN => Yii::_t('statistic.postbacks.type-complain'),
    ];
  }

  /**
   * Список статусов
   * @return array
   */
  public static function getStatusList()
  {
    return [
      self::STATUS_OK => Yii::_t('statistic.postbacks.status_ok'),
      self::STATUS_ERROR => Yii::_t('statistic.postbacks.status_error'),
    ];
  }

  /**
   * @return string
   */
  public function getParsedData()
  {
    return self::getParsedJson($this->data);
  }

  /**
   * Преобразовать json в удобочитаемый текст
   * @param string $json
   * @return string
   */
  private static function getParsedJson($json)
  {
    $result = '';
    $response = Json::decode($json);
    foreach ($response as $key=>$value) {
      $result .= "$key: $value; <br>";
    }
    return $result;
  }

  /**
   * @return User
   */
  public function getUser()
  {
    $userId = (new Query())
      ->select('s.user_id')
      ->from('sources s')
      ->where(['s.id' => $this->source_id])
      ->scalar();

    return $userId
      ? User::findOne($userId)
      : null;
  }

  /**
   * Тип постбека дубль
   * @param $type
   * @return bool
   */
  public static function isDuplicateType($type)
  {
    return $type === self::DUPLICATE;
  }

}