<?php

namespace mcms\promo\models;

use yii\db\ActiveRecord;

/**
 * This is model for table "landing_operator_pay_types"
 *
 * @property integer $id
 * @property integer $landing_id
 * @property integer $operator_id
 * @property integer $landing_pay_type_id
 */
class LandingOperatorPayType extends ActiveRecord
{
  /**
   * @return string
   */
  public static function tableName()
  {
    return '{{%landing_operator_pay_types}}';
  }

  /**
   * @return array
   */
  public function rules()
  {
    return [
      [['id', 'landing_id', 'operator_id', 'landing_pay_type_id'], 'integer'],
    ];
  }
}