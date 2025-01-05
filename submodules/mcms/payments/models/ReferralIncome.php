<?php

namespace mcms\payments\models;

use Yii;
use mcms\common\traits\Translate;

/**
 * This is the model class for table "referral_incomes".
 *
 * @property string $date
 * @property integer $user_id
 * @property integer $referral_id
 * @property integer $is_hold
 * @property string $profit_rub
 * @property string $profit_eur
 * @property string $profit_usd
 * @property integer $referral_percent
 */
class ReferralIncome extends \yii\db\ActiveRecord
{

  public $full_profit;
  public $full_profit_main;
  public $full_profit_hold;

  use Translate;

  const LANG_PREFIX = 'payments.referral-income.';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%referral_incomes}}';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['date', 'user_id', 'referral_id', 'is_hold', 'profit_rub', 'profit_eur', 'profit_usd', 'referral_percent'], 'required'],
      [['date'], 'safe'],
      [['user_id', 'referral_id', 'is_hold', 'referral_percent'], 'integer'],
      [['profit_rub', 'profit_eur', 'profit_usd'], 'number'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels(['date', 'user_id', 'referral_id',
      'is_hold', 'profit_rub', 'profit_eur', 'profit_usd', 'referral_percent']);
  }

  public function getIsHoldName()
  {
    return Yii::_t($this->is_hold ? 'app.common.Yes' : 'app.common.No');
  }

  public function getUser()
  {
    return Yii::$app->getModule('users')->api('user', ['getRelation' => true])->hasOne($this, 'user_id');
  }

  public function getReferral()
  {
    return Yii::$app->getModule('users')->api('user', ['getRelation' => true])->hasOne($this, 'referral_id');
  }
}