<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use Yii;
use yii\behaviors\TimestampBehavior;
use mcms\common\multilang\MultiLangModel;
/**
 * This is the model class for table "traffic_types".
 *
 * @property integer $id
 * @property string $name
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 *
 * @property LandingForbiddenTrafficType[] $landingForbiddenTrafficType
 */
class TrafficType extends MultiLangModel
{

  use Translate;

  const LANG_PREFIX = 'promo.traffic-types.';

  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'traffic_types';
  }

  public function getMultilangAttributes()
  {
    return ['name'];
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
      [['name'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['name'], 'validateArrayRequired'],
      [['name'], 'validateArrayString'],
      [['status'], 'required'],
      [['created_at', 'updated_at', 'status'], 'integer'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'status',
      'name',
      'created_at',
      'updated_at'
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLandingForbiddenTrafficTypes()
  {
    return $this->hasMany(LandingForbiddenTrafficType::class, ['forbidden_traffic_type_id' => 'id']);
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


  public static function getNamesByIds($ids = [])
  {
    $ids = is_array($ids) ? $ids: [$ids];

    return ArrayHelper::getColumn(
      self::find()->where(['in', 'id', $ids])->each(),
      'name'
    );
  }
}