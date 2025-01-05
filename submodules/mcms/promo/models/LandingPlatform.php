<?php

namespace mcms\promo\models;

use Yii;

/**
 * This is the model class for table "landing_platforms".
 *
 * @property integer $id
 * @property integer $landing_id
 * @property integer $platform_id
 *
 * @property Landing $landing
 * @property Platform $platform
 */
class LandingPlatform extends \yii\db\ActiveRecord
{
  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'landing_platforms';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['landing_id', 'platform_id'], 'required'],
      [['landing_id', 'platform_id'], 'integer']
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
      'platform_id' => 'Platform ID',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLanding()
  {
    return $this->hasOne(Landing::class, ['id' => 'landing_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getPlatform()
  {
    return $this->hasOne(Platform::class, ['id' => 'platform_id']);
  }

  static public function findOrCreateModel($landingId, $platformId)
  {
    $found = self::findOne([
      'landing_id' => $landingId,
      'platform_id' => $platformId
    ]);

    return $found ? : new self(
      [
        'landing_id' => $landingId,
        'platform_id' => $platformId
      ]
    );
  }
}