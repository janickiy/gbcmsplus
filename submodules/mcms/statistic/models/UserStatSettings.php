<?php

namespace mcms\statistic\models;

use mcms\user\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_stat_settings".
 *
 * @property integer $user_id
 * @property integer $is_label_stat_enabled
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $dashboard_filters
 *
 * @property User $user
 */
class UserStatSettings extends ActiveRecord
{

  const DEFAULT_IS_LABEL_STAT_ENABLED = 1;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'user_stat_settings';
  }

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['user_id'], 'required'],
      [['user_id', 'is_label_stat_enabled'], 'integer'],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
      ['dashboard_filters', 'string'],
      ['dashboard_filters', 'default', 'value' => json_encode([])],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'user_id' => 'User ID',
      'is_label_stat_enabled' => 'Is Label Stat Enabled',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }
}