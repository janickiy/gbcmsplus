<?php

namespace mcms\payments\models\search;

use mcms\common\traits\Translate;
use mcms\common\helpers\ArrayHelper;
use mcms\payments\models\UserBalancesGroupedByDay;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class UserBalancesGroupedByDaySearch extends UserBalancesGroupedByDay
{
  use Translate;

  const SCENARIO_INDIVIDUAL_SEARCH = 'individual_search';
  const SCENARIO_PROFIT_SEARCH = 'profit_search';
  const SCENARIO_INDIVIDUAL_RESELLER_SEARCH = 'individual_reseller_search';

  public $date_from;
  public $date_to;
  public $profit_from;
  public $profit_to;
  public $profit_rub_from;
  public $profit_rub_to;
  public $profit_eur_from;
  public $profit_eur_to;
  public $profit_usd_from;
  public $profit_usd_to;
  public $currency;
  public $currencyList;

  public function __construct($config = array())
  {
    parent::__construct($config);

    $this->currencyList = ArrayHelper::map(Yii::$app->getModule('promo')
    ->api('mainCurrencies')->getResult(), 'code', 'symbol');
  }

  /**
   * @inheritDoc
   */
  public function rules()
  {
    return [
      [['user_id', 'currency'], 'required', 'on' => [self::SCENARIO_INDIVIDUAL_SEARCH]],
      [['user_id', 'type'], 'integer'],
      [['profit_from', 'profit_to', 'profit_rub_from', 'profit_rub_to',
        'profit_eur_from', 'profit_eur_to', 'profit_usd_from', 'profit_usd_to'], 'number'],
      ['currency', 'in', 'range' => array_keys($this->currencyList)],
      ['profit_to', 'compare', 'compareAttribute' => 'profit_from', 'operator' => '>=', 'skipOnEmpty' => true],
      ['profit_rub_to', 'compare', 'compareAttribute' => 'profit_rub_from', 'operator' => '>=', 'skipOnEmpty' => true],
      ['profit_eur_to', 'compare', 'compareAttribute' => 'profit_eur_from', 'operator' => '>=', 'skipOnEmpty' => true],
      ['profit_usd_to', 'compare', 'compareAttribute' => 'profit_usd_from', 'operator' => '>=', 'skipOnEmpty' => true],
      [['type', 'date_from', 'date_to', 'profit_from', 'profit_to', 'currency',
        'profit_rub_from', 'profit_rub_to', 'profit_eur_from', 'profit_eur_to',
        'profit_usd_from', 'profit_usd_to'], 'safe'],
    ];
  }

  /**
   * @inheritDoc
   */
  public function scenarios()
  {
    $scenarios = Model::scenarios();
    $scenarios[self::SCENARIO_INDIVIDUAL_SEARCH] = [
      'type', 'profit_from', 'profit_to', 'date_from', 'date_to',
    ];
    $scenarios[self::SCENARIO_INDIVIDUAL_RESELLER_SEARCH] = [
      'type', 'profit_rub_from', 'profit_rub_to', 'profit_eur_from',
      'profit_eur_to', 'profit_usd_from', 'profit_usd_to', 'date_from', 'date_to',
    ];
    $scenarios[self::SCENARIO_PROFIT_SEARCH] = [
      'type', 'profit_rub_from', 'profit_rub_to', 'profit_eur_from',
      'profit_eur_to', 'profit_usd_from', 'profit_usd_to','date_from', 'date_to',
    ];
    return $scenarios;
  }

  public function attributeLabels()
  {
    $labels = array_merge(parent::attributeLabels(), [
      'profit_from' => self::translate('attribute-profit-from'),
      'profit_to' => self::translate('attribute-profit-to'),
      'date_from' => self::translate('attribute-date-from'),
      'date_to' => self::translate('attribute-date-to'),
      'currency' => self::translate('attribute-currency'),
    ]);
    return $labels;
  }

  public function search($params)
  {
    $query = UserBalancesGroupedByDay::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => ['defaultOrder' => ['date' => SORT_DESC]]
    ]);

    $this->load($params);

    if (!$this->validate()) {
      if (!in_array($this->scenario, [self::SCENARIO_DEFAULT, self::SCENARIO_PROFIT_SEARCH])) {
        $query->where('0 = 1');
      }
      return $dataProvider;
    }
    $query->andFilterWhere([
      'user_id' => $this->user_id,
      'type' => $this->type
    ]);

    if ($this->date_from) {
      $query->andFilterWhere(['>=', 'date', Yii::$app->formatter->asDate($this->date_from, 'php:Y-m-d')]);
    }
    if ($this->date_to) {
      $query->andFilterWhere(['<=', 'date', Yii::$app->formatter->asDate($this->date_to, 'php:Y-m-d')]);
    }

    $query->andFilterWhere(['>=', 'profit_' . $this->currency, $this->profit_from]);
    $query->andFilterWhere(['<=', 'profit_' . $this->currency, $this->profit_to]);

    $query->andFilterWhere(['>=', 'profit_rub', $this->profit_rub_from]);
    $query->andFilterWhere(['<=', 'profit_rub', $this->profit_rub_to]);
    $query->andFilterWhere(['>=', 'profit_eur', $this->profit_eur_from]);
    $query->andFilterWhere(['<=', 'profit_eur', $this->profit_eur_to]);
    $query->andFilterWhere(['>=', 'profit_usd', $this->profit_usd_from]);
    $query->andFilterWhere(['<=', 'profit_usd', $this->profit_usd_to]);

    return $dataProvider;
  }

  public function searchProfit($params)
  {
    $this->setScenario(self::SCENARIO_PROFIT_SEARCH);
    $query = UserBalancesGroupedByDay::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => ['defaultOrder' => ['date' => SORT_DESC]]
    ]);

    $this->load($params);

    if (!$this->validate()) {
      return $dataProvider;
    }
    $query->andFilterWhere([
      'user_id' => $this->user_id,
      'type' => $this->type
    ]);

    if ($this->date_from) {
      $query->andFilterWhere(['>=', 'date', Yii::$app->formatter->asDate($this->date_from, 'php:Y-m-d')]);
    }
    if ($this->date_to) {
      $query->andFilterWhere(['<=', 'date', Yii::$app->formatter->asDate($this->date_to, 'php:Y-m-d')]);
    }

    $query->andFilterWhere(['>=', 'profit_rub', $this->profit_rub_from]);
    $query->andFilterWhere(['<=', 'profit_rub', $this->profit_rub_to]);
    $query->andFilterWhere(['>=', 'profit_eur', $this->profit_eur_from]);
    $query->andFilterWhere(['<=', 'profit_eur', $this->profit_eur_to]);
    $query->andFilterWhere(['>=', 'profit_usd', $this->profit_usd_from]);
    $query->andFilterWhere(['<=', 'profit_usd', $this->profit_usd_to]);

    return $dataProvider;
  }
}