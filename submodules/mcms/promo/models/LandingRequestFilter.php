<?php

namespace mcms\promo\models;

use mcms\common\traits\Translate;
use mcms\promo\components\ApiHandlersHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "landing_request_filters".
 *
 * @property integer $id
 * @property integer $landing_id
 * @property string $code
 * @property integer $is_active
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Landing $landing
 */
class LandingRequestFilter extends ActiveRecord
{
  use Translate;

  const LANG_PREFIX = 'promo.landing-request-filters.';

  const PROCESSOR_DODOPAY_LABELS = 'dodopay_labels';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'landing_request_filters';
  }

  /**
   * @return array
   */
  public static function getProcessorLabels()
  {
    return [
      self::PROCESSOR_DODOPAY_LABELS => Yii::_t(self::LANG_PREFIX . 'dodopay_labels'),
    ];
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
      [['code', 'landing_id'], 'required'],
      [['landing_id', 'is_active'], 'integer'],
      [['code',], 'string']
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'landing_id',
      'code',
      'is_active',
      'created_at',
      'updated_at',
    ]);
  }

  /**
   * @return string
   */
  public function getProcessorName()
  {
    return $this->code ? self::getProcessorLabels()[$this->code] : null;
  }

  /**
   * @return ActiveQuery
   */
  public function getLanding()
  {
    return $this->hasOne(Landing::class, ['id' => 'landing_id']);
  }

  /**
   * @param bool $insert
   * @param array $changedAttributes
   */
  public function afterSave($insert, $changedAttributes)
  {
    ApiHandlersHelper::clearCache('LandingFilters' . $this->landing_id);

    parent::afterSave($insert, $changedAttributes);
  }

  /**
   *
   */
  public function afterDelete()
  {
    ApiHandlersHelper::clearCache('LandingFilters' . $this->landing_id);

    parent::afterDelete();
  }
}
