<?php

namespace mcms\notifications\models;

use Yii;

/**
 * This is the model class for table "users_push_tokens".
 *
 * @property integer $user_id
 * @property string $token
 */
class UserPushToken extends \yii\db\ActiveRecord
{
  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'users_push_tokens';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['user_id', 'token'], 'required'],
      [['user_id'], 'integer'],
      [['token'], 'string', 'max' => 255],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'user_id' => 'User ID',
      'token' => 'Token',
    ];
  }

  /**
   * Есть ли у пользователя хоть один token
   * @param $user_id
   * @return bool
   */
  public static function isUserHaveToken($user_id)
  {
    return (bool)self::findOne(['user_id' => $user_id]);
  }
}
