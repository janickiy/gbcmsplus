<?php

namespace mcms\promo\models;

use mcms\user\models\User;
use Yii;

/**
 * This is the model class for table "{{%visible_landings_partners}}".
 *
 * @property integer $user_id
 * @property integer $landing_id
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property User $createdBy
 * @property Landing $landing
 * @property User $user
 */
class VisibleLandingPartner extends \yii\db\ActiveRecord
{
  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%visible_landings_partners}}';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['user_id', 'landing_id', 'created_by', 'created_at'], 'required'],
      [['user_id', 'landing_id', 'created_by', 'created_at', 'updated_at'], 'integer'],
      [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
      [['landing_id'], 'exist', 'skipOnError' => true, 'targetClass' => Landing::class, 'targetAttribute' => ['landing_id' => 'id']],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'user_id' => 'User ID',
      'landing_id' => 'Landing ID',
      'created_by' => 'Created By',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCreatedBy()
  {
    return $this->hasOne(User::class, ['id' => 'created_by']);
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
  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }
}
