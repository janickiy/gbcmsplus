<?php

namespace mcms\payments\models;

use rgk\utils\behaviors\TimestampBehavior;


/**
 * Модель для логирования смены валюты партнером
 * @property integer $user_id Id партнера
 * @property string $currency Новая валюта партнера
 * @property integer $created_at Timestamp смены валюты на новую
 */
class CurrencyLog extends \yii\db\ActiveRecord
{
  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      [
        'class' => TimestampBehavior::class,
        'updatedAtAttribute' => null,
        'skipOnChanged' => true,
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'currency_log';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['user_id', 'currency'], 'required'],
      [['user_id'], 'integer'],
      [['currency'], 'string'],
    ];
  }

}