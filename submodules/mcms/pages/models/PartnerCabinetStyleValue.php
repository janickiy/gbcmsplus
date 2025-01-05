<?php

namespace mcms\pages\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "partner_cabinet_style_values".
 *
 * @property integer $id
 * @property integer $field_id
 * @property integer $style_id
 * @property string $value
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property PartnerCabinetStyleField $field
 * @property PartnerCabinetStyle $style
 */
class PartnerCabinetStyleValue extends \yii\db\ActiveRecord
{
  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
      BlameableBehavior::class,
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'partner_cabinet_style_values';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['value'], 'filter', 'filter' => function ($item) {
        return strtr($item, ['}' => '', '{' => '']);
      }],
      ['id', 'safe'],
      [['field_id', 'style_id'], 'required'],
      [['field_id', 'style_id','created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
      [['value'], 'string'],
      [['field_id'], 'exist', 'skipOnError' => true, 'targetClass' => PartnerCabinetStyleField::class, 'targetAttribute' => ['field_id' => 'id']],
      [['field_id'], 'exist', 'skipOnError' => true, 'targetClass' => PartnerCabinetStyle::class, 'targetAttribute' => ['style_id' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'field_id' => 'Field ID',
      'style_id' => 'Style ID',
      'value' => 'Value',
      'created_by' => 'Created By',
      'updated_by' => 'Updated By',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getField()
  {
    return $this->hasOne(PartnerCabinetStyleField::class, ['id' => 'field_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getStyle()
  {
    return $this->hasOne(PartnerCabinetStyle::class, ['id' => 'style_id']);
  }
}
