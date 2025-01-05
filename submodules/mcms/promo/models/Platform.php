<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\models\search\PlatformSearch;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Url;

/**
 * This is the model class for table "platforms".
 *
 * @property integer $id
 * @property string $name
 * @property string $match_string
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 *
 * @property LandingPlatform[] $landingPlatform
 */
class Platform extends \yii\db\ActiveRecord
{

  use Translate;

  const LANG_PREFIX = 'promo.platforms.';

  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'platforms';
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
      [['name', 'match_string', 'status'], 'required'],
      [['created_at', 'updated_at', 'status'], 'integer'],
      [['name', 'match_string'], 'string', 'max' => 100]
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
      'match_string',
      'created_at',
      'updated_at',
      'status',
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLandingPlatforms()
  {
    return $this->hasMany(LandingPlatform::class, ['platform_id' => 'id']);
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
    ApiHandlersHelper::clearCache('Platforms');

    parent::afterSave($insert, $changedAttributes);
  }

  public static function getViewUrl($id, $asString = false)
  {
    $formName = (new PlatformSearch)->formName();
    $arr = ['/promo/platforms/index', $formName . '[id]' => $id, $formName . '[status]' => ''];
    return $asString ? Url::to($arr) : $arr;
  }

  /**
   * @return string
   */
  public function getStringInfo()
  {
    return Yii::$app->formatter->asText($this->name);
  }
}
