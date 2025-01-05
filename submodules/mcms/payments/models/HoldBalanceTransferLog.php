<?php

namespace mcms\payments\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "hold_balance_transfer_logs".
 *
 * @property integer $landing_id
 * @property integer $user_id
 * @property string $date
 * @property string $currency
 * @property string $amount
 * @property integer $created_at
 */
class HoldBalanceTransferLog extends \yii\db\ActiveRecord
{
  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'hold_balance_transfer_logs';
  }

  public function behaviors()
  {
    return [
      [
        'class' => TimestampBehavior::class,
        'updatedAtAttribute' => false
      ]
    ];
  }


  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['landing_id', 'user_id', 'date', 'currency', 'amount'], 'required'],
      [['landing_id', 'user_id', 'created_at'], 'integer'],
      [['date'], 'safe'],
      [['amount'], 'number'],
      [['amount'], 'compare', 'compareValue' => 0, 'operator' => '>'],
      [['currency'], 'string', 'max' => 3],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'landing_id' => 'Landing ID',
      'user_id' => 'User ID',
      'date' => 'Date',
      'currency' => 'Currency',
      'amount' => 'Amount',
      'created_at' => 'Created At',
    ];
  }

  public static function isExist($landingId, $userId, $date, $currency)
  {
    return self::find()->where([
      'and',
      ['=', 'landing_id', $landingId],
      ['=', 'user_id', $userId],
      ['=', 'date', $date],
      ['=', 'currency', $currency],
    ])->exists();
  }
}
