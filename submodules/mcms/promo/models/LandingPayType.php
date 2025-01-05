<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use mcms\promo\components\ApiHandlersHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

/**
 * This is the model class for table "landing_pay_types".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 *
 */
class LandingPayType extends \yii\db\ActiveRecord
{

  use Translate;

  const LANG_PREFIX = 'promo.landing-pay-types.';

  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'landing_pay_types';
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
      [['name', 'status', 'code'], 'required'],
      [['created_at', 'updated_at', 'status'], 'integer'],
      [['name', 'code'], 'string', 'max' => 100]
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
      'code',
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

  public function afterSave($insert, $changedAttributes)
  {

    ApiHandlersHelper::clearCache('PayTypes');

    $this->invalidateCache();

    parent::afterSave($insert, $changedAttributes);
  }

  public function invalidateCache()
  {
    TagDependency::invalidate(Yii::$app->cache, ['landing']);
  }

  public function afterDelete()
  {
    $this->invalidateCache();
    parent::afterDelete();
  }

  /**
   * @return string
   */
  public function getStringInfo()
  {
    return Yii::$app->formatter->asText($this->name);
  }
}