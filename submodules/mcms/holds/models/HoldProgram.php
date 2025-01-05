<?php

namespace mcms\holds\models;

use mcms\payments\models\UserPaymentSetting;
use mcms\user\models\User;
use rgk\utils\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "hold_programs".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $is_default
 * @property User[] $users
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property HoldProgramRule[] $holdProgramRules
 */
class HoldProgram extends \yii\db\ActiveRecord
{
  const SCENARIO_CHANGE_IS_DEFAULT = 'scenario_change_is_default';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'hold_programs';
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
      [['name', 'description'], 'required'],
      [['description'], 'string'],
      [['is_default'], 'integer'],
      [['name'], 'string', 'max' => 255],
      ['is_default', 'filter', 'filter' => 'intval'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return array_merge(
      parent::scenarios(),
      [
        self::SCENARIO_CHANGE_IS_DEFAULT => ['is_default'],
      ]
    );
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'name' => Yii::_t('holds.main.hold_program_name'),
      'description' => Yii::_t('holds.main.hold_program_description'),
      'is_default' => Yii::_t('holds.main.hold_program_is_default'),
    ];
  }

  /**
   * Если выставлен is_default = 1, всем остальным сбрасываем
   * @param bool $insert
   * @return bool
   */
  public function beforeSave($insert)
  {
    if ($this->isAttributeChanged('is_default') && $this->is_default === 1) {
      $this->resetIsDefault();
    }
    return parent::beforeSave($insert);
  }

  /**
   * Везде ставим is_default = 0
   */
  private function resetIsDefault()
  {
    $models = self::findAll(['is_default' => 1]);
    foreach ($models as $model) {
      $model->setScenario(self::SCENARIO_CHANGE_IS_DEFAULT);
      $model->is_default = 0;
      $model->save();
    }
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getHoldProgramRules()
  {
    return $this->hasMany(HoldProgramRule::class, ['hold_program_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUsers()
  {
    return $this
      ->hasMany(User::class, ['id' => 'user_id'])
      ->viaTable(UserPaymentSetting::tableName(), ['hold_program_id' => 'id']);
  }
}
