<?php

namespace mcms\promo\models;

use mcms\common\traits\Translate;
use Yii;

/**
 * This is the model class for table "competitive_access_provider".
 *
 * @property integer $id
 * @property integer $external_id
 * @property integer $day_limit
 * @property integer $external_provider_id
 * @property integer $operator_id
 * @property integer $service_id
 * @property integer $landing_id
 * @property integer $active_from
 * @property integer $is_blocked
 * @property integer $status
 * @property integer $provider_id
 * @property integer $sync_at
 *
 * @property Operator|null $operator
 * @property ExternalProvider $externalProvider
 * @property Service $service
 */
class Cap extends \yii\db\ActiveRecord
{
  use Translate;

  const LANG_PREFIX = 'promo.caps.';

  const STATUS_INACTIVE = 0;
  const STATUS_ACTIVE = 1;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'competitive_access_provider';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['day_limit', 'external_id'], 'required'],
      [['day_limit', 'external_provider_id', 'operator_id', 'service_id', 'landing_id', 'active_from', 'status', 'is_blocked', 'provider_id', 'sync_at', 'external_id'], 'integer'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'day_limit',
      'is_blocked',
      'external_provider_id',
      'operator_id',
      'country_id',
      'landing_id',
      'service_id',
      'active_from',
    ]);
  }

  /**
   * @return string|null
   */
  public function getOperatorLink()
  {
    return $this->operator ? $this->operator->getViewLink() : null;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLanding()
  {
    return $this->hasOne(Landing::class, ['send_id' => 'landing_id', 'provider_id' => 'provider_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getOperator()
  {
    return $this->hasOne(Operator::class, ['id' => 'operator_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getExternalProvider()
  {
    return $this->hasOne(ExternalProvider::class, ['id' => 'external_provider_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getService()
  {
    return $this->hasOne(Service::class, ['id' => 'service_id']);
  }

  /**
   * @return string|null
   */
  public function getCountryLink()
  {
    if ($this->externalProvider) {
      return $this->externalProvider->getCountryLink();
    }
    if ($this->operator) {
      return $this->operator->getCountryLink();
    }

    return null;
  }
}
