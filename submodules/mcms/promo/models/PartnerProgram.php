<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "partner_programs".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $rebill_percent
 * @property string $buyout_percent
 * @property string $cpa_profit_rub
 * @property string $cpa_profit_eur
 * @property string $cpa_profit_usd
 * @property integer $landing_id
 * @property integer $operator_id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Operator $operator
 * @property Landing $landing
 *
 * @property $userIds
 */
class PartnerProgram extends \yii\db\ActiveRecord
{
  use Translate;

  const LANG_PREFIX = 'promo.partner_programs.';

  private static $_dropdownList = [];

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
    return 'partner_programs';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['name'], 'string', 'max' => 128],
      [['description'], 'string'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'name',
      'description',
      'created_at',
      'updated_at',
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUserPromoSettings()
  {
    return $this->hasMany(UserPromoSetting::class, ['partner_program_id' => 'id']);
  }

  /**
   * @return array
   */
  public function getUserIds()
  {
    return array_map(function ($item) {
      return $item->user_id;
    }, $this->userPromoSettings);
  }

  /**
   * @return array
   */
  public function getAutoSyncUserIds()
  {
    return array_map(function ($item) {
      return $item->user_id;
    }, $this->getUserPromoSettings()->andWhere(['partner_program_autosync' => 1])->all());
  }

  /**
   * @return array|\yii\db\ActiveRecord[]
   */
  public static function dropdown()
  {
    !self::$_dropdownList && self::$_dropdownList = ArrayHelper::map(self::find()->all(), 'id', 'name');
    return self::$_dropdownList;
  }
}
