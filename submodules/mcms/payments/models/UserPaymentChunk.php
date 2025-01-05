<?php

namespace mcms\payments\models;

use mcms\common\traits\Translate;
use rgk\utils\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "payment_chunks".
 *
 * @property integer $id
 * @property integer $payment_id
 * @property integer $external_id
 * @property string $amount
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property UserPayment $payment
 */
class UserPaymentChunk extends ActiveRecord
{

  use Translate;

  const LANG_PREFIX = 'payments.reseller-profit-log.chunk_';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'user_payment_chunks';
  }

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      [
        'class' => TimestampBehavior::class,
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['payment_id', 'amount', 'external_id'], 'required'],
      [['payment_id'], 'integer'],
      [['amount'], 'number'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels([
      'id',
      'amount',
      'created_at',
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getPayment()
  {
    return $this->hasOne(UserPayment::class, ['id' => 'payment_id']);
  }

}