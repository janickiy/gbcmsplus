<?php

namespace mcms\payments\models\search;

use Yii;
use mcms\common\helpers\ArrayHelper;
use mcms\payments\models\ReferralIncome;
use yii\db\Expression;
use yii\data\ActiveDataProvider;

class ReferralIncomeSearch extends ReferralIncome
{

  const SCENARIO_PARTNER_REFERRAL_SEARCH = 'partner_referral_search';

  const REFERRALS_ALL = 0;
  const REFERRALS_ACTIVE = 1;
  const REFERRALS_INACTIVE = 2;

  public $date_from;
  public $date_to;
  public $user_id;
  public $is_hold;
  public $currency;
  public $active_referrals;

  /**
   * @inheritdoc
   */
  public function init()
  {
    parent::init();
    if ($this->scenario === self::SCENARIO_PARTNER_REFERRAL_SEARCH && $this->date_from === null && $this->date_to === null) {
      $this->date_from = Yii::$app->formatter->asDate('now -7 days', 'dd.MM.yyyy');
      $this->date_to = Yii::$app->formatter->asDate('now', 'dd.MM.yyyy');
    }
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_PARTNER_REFERRAL_SEARCH => ['date_from', 'date_to', 'active_referrals']
    ]);
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['user_id', 'currency'], 'required'],
      [['date_from', 'date_to', 'is_hold', 'active_referrals'], 'safe'],
      [['user_id', 'is_hold', 'active_referrals'], 'integer'],
      [['date_from', 'date_to'], 'date', 'min' => '01.01.2000', 'max' => '31.12.2030', 'format' => 'dd.MM.yyyy', 'on' => self::SCENARIO_PARTNER_REFERRAL_SEARCH],
    ];
  }

  /**
   * Получение опций для дродауна, по каким рефералам искать
   * @return type
   */
  public function getActiveReferralsDropdown()
  {
    return [
      self::REFERRALS_ALL => self::translate('referrals-all'),
      self::REFERRALS_ACTIVE => self::translate('referrals-active'),
      self::REFERRALS_INACTIVE => self::translate('referrals-inactive'),
    ];
  }

  /**
   * Поиск сгруппированных балансов для всех рефералов пользователя
   * @param array $params
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    $query = ReferralIncome::find();
    $query->with('referral');
    $query->addSelect(static::tableName() . '.*');

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $this->load($params);

    if (!$this->validate()) {
      $query->where('0 = 1');
      return $dataProvider;
    }

    $query->andFilterWhere(['user_id' => $this->user_id]);

    if ($this->date_from) {
      $query->andFilterWhere(['>=', 'date',
        Yii::$app->formatter->asDate($this->date_from, 'php:Y-m-d')
      ]);
    }
    if ($this->date_to) {
      $query->andFilterWhere(['<=', 'date',
        Yii::$app->formatter->asDate($this->date_to, 'php:Y-m-d')
      ]);
    }

    if ($this->active_referrals != self::REFERRALS_ALL) {
      $activeReferralsIds = Yii::$app->getModule('statistic')->api('activeReferrals', [
        'userId' => $this->user_id,
        'startDate' => $this->date_from,
        'endDate' => $this->date_to,
      ])->getResult();

      if (empty($activeReferralsIds) && $this->active_referrals == self::REFERRALS_ACTIVE) {
        $query->where('0 = 1');
      } else {
        $query->andFilterWhere([$this->active_referrals == self::REFERRALS_ACTIVE ? 'in' : 'not in', 'referral_id', $activeReferralsIds]);
      }
    }

    $profitField = '{{profit_' . $this->currency . '}}';
    $query->addSelect([
      'full_profit_main' => new Expression('SUM(IF(is_hold = 0, ' . $profitField . ', 0))'),
      'full_profit_hold' => new Expression('SUM(IF(is_hold = 1, ' . $profitField . ', 0))'),
    ]);

    $query->groupBy('referral_id');

    return $dataProvider;
  }

  /**
   * Получение суммированного дохода по всем рефералам пользователя
   * @param array $params
   * @return ActiveDataProvider
   */
  public function getTotalAmount($params)
  {
    $query = ReferralIncome::find();
    $this->load($params);

    if (!$this->validate()) {
      return 0;
    }

    $query->select([
      'full_profit' => new Expression('SUM({{profit_' . $this->currency . '}})')
    ]);

    $query->andFilterWhere([
      'is_hold' => $this->is_hold,
      'user_id' => $this->user_id,
    ]);

    if ($this->date_from) {
      $query->andFilterWhere(['>=', 'date',
        Yii::$app->formatter->asDate($this->date_from, 'php:Y-m-d')
      ]);
    }
    if ($this->date_to) {
      $query->andFilterWhere(['<=', 'date',
        Yii::$app->formatter->asDate($this->date_to, 'php:Y-m-d')
      ]);
    }

    if ($this->active_referrals != self::REFERRALS_ALL) {
      $activeReferralsIds = Yii::$app->getModule('statistic')->api('activeReferrals', [
        'userId' => $this->user_id,
        'startDate' => $this->date_from,
        'endDate' => $this->date_to,
      ])->getResult();

      if (empty($activeReferralsIds) && $this->active_referrals == self::REFERRALS_ACTIVE) {
        $query->where('0 = 1');
      } else {
        $query->andFilterWhere([$this->active_referrals == self::REFERRALS_ACTIVE ? 'in' : 'not in', 'referral_id', $activeReferralsIds]);
      }
    }

    return (float) $query->scalar();
  }

}
