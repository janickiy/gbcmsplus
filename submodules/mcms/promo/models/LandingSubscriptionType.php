<?php

namespace mcms\promo\models;

use Yii;
use mcms\common\traits\Translate;
use yii\behaviors\TimestampBehavior;
use mcms\common\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%landing_subscription_types}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 */
class LandingSubscriptionType extends \yii\db\ActiveRecord
{

  use Translate;

  const LANG_PREFIX = 'promo.landing-subscription-types.';

  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;

  // коды типов подписок
  const CODE_SUBSCRIPTION = 'sub';
  const CODE_ONETIME = 'onetime';


  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'landing_subscription_types';
  }

  /**
   * @return array
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class
    ];
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['name', 'status'], 'required'],
      [['created_at', 'updated_at', 'status'], 'integer'],
      [['name'], 'string', 'max' => 100]
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'name',
      'status',
      'created_at',
      'updated_at'
    ]);
  }

  public function getStatuses($status = null)
  {
    $list = [
      self::STATUS_INACTIVE => self::translate('status-inactive'),
      self::STATUS_ACTIVE => self::translate('status-active')
    ];
    return isset($status) ? ArrayHelper::getValue($list, $status, null) : $list;
  }


  public function getCurrentStatusName()
  {
    return $this->getStatuses($this->status);
  }

  /**
   * @return array
   */
  static function getStatusColors()
  {
    return [
      self::STATUS_ACTIVE => '',
      self::STATUS_INACTIVE => 'danger',
    ];
  }

  /**
   * @return bool
   */
  public function isDisabled()
  {
    return $this->status !== self::STATUS_ACTIVE;
  }

  /**
   * @return bool
   */
  public function isEnabled()
  {
    return $this->status === self::STATUS_ACTIVE;
  }

  /**
   * @return $this
   */
  public function setEnabled()
  {
    $this->status = self::STATUS_ACTIVE;
    return $this;
  }

  /**
   * @return $this
   */
  public function setDisabled()
  {
    $this->status = self::STATUS_INACTIVE;
    return $this;
  }
}
