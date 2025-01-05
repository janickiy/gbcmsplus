<?php

namespace mcms\promo\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%operator_ipv6}}".
 *
 * @property integer $id
 * @property integer $operator_id
 * @property integer $ip
 * @property integer $mask
 *
 * @property Operator $operator
 */
class OperatorIpv6 extends ActiveRecord
{
  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%operator_ipv6}}';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['operator_id', 'ip', 'mask'], 'required'],
      [['ip'], 'ip', 'ipv4' => false, 'ipv6' => true],
      [['operator_id', 'mask'], 'integer'],
      [['operator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Operator::class, 'targetAttribute' => ['operator_id' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'operator_id' => Yii::_t('promo.operators.ip-attribute-operator_id'),
      'ip' => Yii::_t('promo.operators.ip-attribute-ip'),
      'mask' => Yii::_t('promo.operators.ip-attribute-mask'),
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getOperator()
  {
    return $this->hasOne(Operator::class, ['id' => 'operator_id']);
  }
}
