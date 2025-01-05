<?php

namespace mcms\promo\models;

use Yii;

/**
 * This is the model class for table "{{%operator_ips}}".
 *
 * @property integer $id
 * @property integer $operator_id
 * @property integer $from_ip
 * @property integer $to_ip
 * @property integer $mask
 *
 * @property Operator $operator
 */
class OperatorIp extends \yii\db\ActiveRecord
{
  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%operator_ips}}';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['operator_id', 'from_ip', 'to_ip'], 'required'],
      [['from_ip', 'to_ip'], 'ip', 'ipv4' => true, 'ipv6' => false],
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

  public function getValidateAttributes()
  {
    $attributes = $this->getAttributes();
    unset($attributes['operator_id']);
    return $attributes;
  }

  static public function findOrCreateModel($id)
  {
    return $id && ($model = self::findOne($id)) ? $model : new self;
  }

  public function beforeSave($insert)
  {
    $this->from_ip = ip2long($this->from_ip);
    $this->to_ip = ip2long($this->to_ip);
    return parent::beforeSave($insert);
  }

  public function afterFind()
  {
    $this->from_ip = long2ip($this->from_ip);
    $this->to_ip = long2ip($this->to_ip);
  }


  public function tryToFind()
  {
    $filter = array_filter($this->attributes);
    $filter['from_ip'] = ip2long($filter['from_ip']);
    $this->to_ip && $filter['to_ip'] = ip2long($filter['to_ip']);

    return self::findOne($filter) ?: $this;
  }
}
