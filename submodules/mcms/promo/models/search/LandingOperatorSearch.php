<?php

namespace mcms\promo\models\search;

use mcms\promo\models\Country;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\LandingUnblockRequest;
use mcms\promo\models\Operator;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * LandingOperatorSearch represents the model behind the search form about `mcms\promo\models\LandingOperator`.
 */
class LandingOperatorSearch extends LandingOperator
{

  const SCENARIO_STAT_FILTERS = 'stat_filters';
  public $onlyActive = true;
  public $onlyActiveCountries;
  public $onlyActiveOperators;

  public $orderByCountry = false;
  public $isOrderLandingsDirectionDesc = false;
  public $isOrderLandingsOpenFirst = false;
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['operator_id', 'days_hold', 'default_currency_id'], 'integer'],
      [['landing_id'], 'integer', 'except' => self::SCENARIO_STAT_FILTERS],
      [['landing_id'], 'each', 'rule' => ['integer'], 'on' => self::SCENARIO_STAT_FILTERS],
      [
        [
          'default_currency_rebill_price',
          'buyout_price_usd',
          'buyout_price_eur',
          'buyout_price_rub',
          'rebill_price_usd',
          'rebill_price_eur',
          'rebill_price_rub'
        ],
        'number'
      ],
      [['onlyActiveCountries', 'onlyActiveOperators', 'onlyActive'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_STAT_FILTERS => ['landing_id'],
    ]);
  }

  /**
   * Creates data provider instance with search query applied
   *
   * @param array $params
   *
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    $query = LandingOperator::find();

    $query->joinWith([
      'operator',
      'landing',
      'landing.landingUnblockRequestCurrentUser' => function(\yii\db\ActiveQuery $q) {
        $q->onCondition(['user_id' => Yii::$app->user->getId()]);
      },
      'landing.activeLandingOperators',
//      'landing.landingPlatforms',
//      'landing.landingPlatforms.platform',
//      'landing.forbiddenTrafficTypes',
    ]);

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider(
      [
        'query' => $query
      ]
    );

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    if ($this->onlyActive) {
      $query->andWhere([static::tableName() . '.is_deleted' => 0]);
    }

    if ($this->onlyActiveOperators) {
      $query->joinWith(['operator']);
      $query->andWhere([Operator::tableName() . '.status' => Operator::STATUS_ACTIVE]);
    }

    if ($this->onlyActiveCountries) {
      $query->joinWith(['operator.country']);
      $query->andWhere([Country::tableName() . '.status' => Country::STATUS_ACTIVE]);
    }

    if($landingStatus = ArrayHelper::getValue($params, 'landing_status')) {
      $query->andWhere(['landings.status' => $landingStatus]);
    }

    if (ArrayHelper::getValue($params, 'hide_inaccessible') === true) {
      $query->andWhere(['!=', 'landings.access_type', Landing::ACCESS_TYPE_HIDDEN]);
    }

    // grid filtering conditions
    $query->andFilterWhere(
      [
        static::tableName() . '.landing_id' => $this->landing_id,
        static::tableName() . '.operator_id' => $this->operator_id,
        static::tableName() . '.days_hold' => $this->days_hold,
        static::tableName() . '.default_currency_id' => $this->default_currency_id,
        static::tableName() . '.default_currency_rebill_price' => $this->default_currency_rebill_price,
        static::tableName() . '.buyout_price_usd' => $this->buyout_price_usd,
        static::tableName() . '.buyout_price_eur' => $this->buyout_price_eur,
        static::tableName() . '.buyout_price_rub' => $this->buyout_price_rub,
        static::tableName() . '.rebill_price_usd' => $this->rebill_price_usd,
        static::tableName() . '.rebill_price_eur' => $this->rebill_price_eur,
        static::tableName() . '.rebill_price_rub' => $this->rebill_price_rub,
      ]
    );

    $orderByUnblockRequestStatus = [];

    if ($this->isOrderLandingsOpenFirst) {
      $unblockRequestStatues = implode(',', [
        LandingUnblockRequest::STATUS_DISABLED,
        LandingUnblockRequest::STATUS_MODERATION,
        LandingUnblockRequest::STATUS_UNLOCKED,
      ]);

      $orderByUnblockRequestStatus[] = new Expression("FIELD(landing_unblock_requests.status, {$unblockRequestStatues}) DESC");
    }

    $orderLandingsDirection = $this->isOrderLandingsDirectionDesc ? SORT_DESC : SORT_ASC;
    $query->orderBy(array_merge($orderByUnblockRequestStatus, ['landings.id' => $orderLandingsDirection]));

    if ($this->orderByCountry) {
      $query->joinWith(['operator.country']);
      $query->select(self::tableName(). '.*, ' . Country::tableName().'.name as country');
      $orderBy['country'] = SORT_ASC;
      $query->orderBy(array_merge($orderByUnblockRequestStatus, [
        'country' => SORT_ASC,
        'landings.id' => $orderLandingsDirection
      ]));
    }

    return $dataProvider;
  }
}
