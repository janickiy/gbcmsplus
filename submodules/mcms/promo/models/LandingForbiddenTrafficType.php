<?php

namespace mcms\promo\models;

use Yii;

/**
 * This is the model class for table "landing_forbidden_traffic_types".
 *
 * @property integer $id
 * @property integer $landing_id
 * @property integer $forbidden_traffic_type_id
 *
 * @property ForbiddenTrafficType $forbiddenTrafficType
 * @property Landing $landing
 */
class LandingForbiddenTrafficType extends \yii\db\ActiveRecord
{
  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'landing_forbidden_traffic_types';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['landing_id', 'forbidden_traffic_type_id'], 'required'],
      [['landing_id', 'forbidden_traffic_type_id'], 'integer']
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'landing_id' => 'Landing ID',
      'forbidden_traffic_type_id' => 'Forbidden Traffic Type ID',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getForbiddenTrafficType()
  {
    return $this->hasOne(ForbiddenTrafficType::class, ['id' => 'forbidden_traffic_type_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLanding()
  {
    return $this->hasOne(Landing::class, ['id' => 'landing_id']);
  }

  static public function findOrCreateModel($landingId, $typeId)
  {
    $found = self::findOne([
      'landing_id' => $landingId,
      'forbidden_traffic_type_id' => $typeId
    ]);

    return $found ? : new self(
      [
        'landing_id' => $landingId,
        'forbidden_traffic_type_id' => $typeId
      ]
    );
  }
}