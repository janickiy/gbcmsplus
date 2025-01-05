<?php

namespace mcms\promo\models\search;

use mcms\promo\models\LandingCategory;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\Country;
use mcms\promo\models\LandingSetItem;
use mcms\promo\models\Operator;
use mcms\promo\models\Provider;
use mcms\promo\models\SourceOperatorLanding;
use mcms\promo\Module;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\Landing;
use yii\db\Expression;

/**
 * LandingSearch represents the model behind the search form about `mcms\promo\models\Landing`.
 */
class LandingSearch extends Landing
{
  /** @var int Исключить лендинги определенного набора */
  public $excludeSetId;
  public $excludeSourceId;
  public $operators;
  public $countries;
  public $queryName;
  public $createdFrom;
  public $createdTo;
  public $operatorRequired;
  public $onlyActiveCountries;
  public $onlyActiveOperators;

  const MIN_LENGTH_SEARCH_FROM_BEGINING = 3;
  const SCENARIO_ADMIN = 'admin';

  public function rules()
  {
    return [
      [['category_id', 'offer_category_id', 'provider_id', 'access_type', 'status', 'rating', 'auto_rating', 'created_by', 'created_at', 'updated_at'], 'integer'],
      ['id', 'each', 'rule' => ['integer'], 'when' => function() { return is_array($this->id); }],
      ['id', 'integer', 'when' => function() { return !is_array($this->id); }],
      [['name', 'code', 'image_src', 'description', 'operators', 'countries', 'queryName', 'createdFrom', 'createdTo', 'operatorRequired',
        'onlyActiveCountries', 'onlyActiveOperators'], 'safe'],
      [['excludeSetId', 'excludeSourceId'], 'integer'],
    ];
  }

  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_ADMIN => ['id', 'code', 'name', 'offer_category_id', 'category_id', 'provider_id', 'countries', 'operators', 'status', 'access_type', 'created_at', 'createdFrom', 'createdTo'],
    ]);
  }

  /**
   * @param array $params Параметры поиска
   * @param bool $formName @see Model::load()
   * @return ActiveDataProvider
   */
  public function search($params, $formName = null)
  {
    $query = Landing::find()->distinct();

    $query->joinWith([
      'category',
      'provider'
    ]);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'id' => SORT_DESC,
        ],
      ],
    ]);

    $dataProvider->sort->attributes['category.name'] = [
      'asc' => [LandingCategory::tableName() . '.`name`' => SORT_ASC],
      'desc' => [LandingCategory::tableName() . '.`name`' => SORT_DESC],
    ];

    $dataProvider->sort->attributes['provider.name'] = [
      'asc' => [Provider::tableName() . '.`name`' => SORT_ASC],
      'desc' => [Provider::tableName() . '.`name`' => SORT_DESC],
    ];

    if ($this->scenario !== self::SCENARIO_ADMIN) {
      $this->status = self::STATUS_ACTIVE;
    }

    $this->load($params, $formName);

    if (!$this->validate()) return $dataProvider;

    if ($this->excludeSetId) {
      $query->andFilterWhere([
        'not in',
        self::tableName() . '.' . 'id',
        LandingSetItem::find()
          ->select('landing_id')
          ->where(['set_id' => $this->excludeSetId, 'operator_id' => $this->operators])
          ->column()
      ]);
    }
    if ($this->excludeSourceId) {
      $query->andFilterWhere([
        'not in',
        self::tableName() . '.' . 'id',
        SourceOperatorLanding::find()
          ->select('landing_id')
          ->where(['source_id' => $this->excludeSourceId, 'operator_id' => $this->operators])
          ->column()
      ]);
    }

    $query->andFilterWhere([
      self::tableName() . '.' . 'id' => $this->id,
      self::tableName() . '.' . 'offer_category_id' => $this->offer_category_id,
      self::tableName() . '.' . 'category_id' => $this->category_id,
      self::tableName() . '.' . 'provider_id' => $this->provider_id,
      self::tableName() . '.' . 'access_type' => $this->access_type,
      self::tableName() . '.' . 'status' => $this->status,
      self::tableName() . '.' . 'rating' => $this->rating,
      self::tableName() . '.' . 'auto_rating' => $this->auto_rating,
      self::tableName() . '.' . 'created_by' => $this->created_by,
    ]);

    if ($this->onlyActiveOperators) {
      $query->joinWith(['landingOperator.operator']);
      $query->andWhere([Operator::tableName() . '.status' => Operator::STATUS_ACTIVE]);
    }

    if ($this->operators) {
      $query->joinWith('landingOperator');
      $query->andFilterWhere(['in', LandingOperator::tableName() . '.' . 'operator_id', $this->operators]);
      $query->andWhere([LandingOperator::tableName() . '.is_deleted' => 0]);
    }

    if ($this->onlyActiveCountries) {
      $query->joinWith(['landingOperator.operator.country']);
      $query->andWhere([Country::tableName() . '.status' => Country::STATUS_ACTIVE]);
    }

    if ($this->countries) {
      $query->joinWith(['landingOperator.operator']);
      $query->andFilterWhere(['in', Operator::tableName() . '.' . 'country_id', $this->countries]);
    }

    $query->andFilterWhere(['like', self::tableName() . '.' . 'name', $this->name])
      ->andFilterWhere([
        'like',
        'CONCAT(' . Provider::tableName() . '.' . 'code' . ', ' . self::tableName() . '.' . 'send_id)',
        $this->code
      ])
      ->andFilterWhere(['like', self::tableName() . '.' . 'description', $this->description]);

    if (!Yii::$app->user->can(Module::PERMISSION_CAN_VIEW_BLOCKED_LANDINGS)) {
      $query->andFilterWhere(['<>', self::tableName() . '.' . 'status', self::STATUS_BLOCKED]);
    }

    if ($this->queryName !== null) {
      $query
        ->andWhere(['!=', self::tableName() . '.' . 'id', $this->queryName])
        ->andWhere([
          'or',
          mb_strlen($this->queryName) > self::MIN_LENGTH_SEARCH_FROM_BEGINING
            ? ['like', self::tableName() . '.' . 'name', $this->queryName]
            : ['like', self::tableName() . '.' . 'name', $this->queryName . '%', false]
          ,
          ['like', self::tableName() . '.' . 'id', $this->queryName],
        ]);

      // При поиске, если оператор не выбран, не нужно возвращать пользователю лендинги
      if ($this->operatorRequired && empty($this->operators)) {
        $query->where('0=1');
      }
    }

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.' . 'created_at', strtotime($this->createdFrom)]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<=', self::tableName() . '.' . 'created_at', strtotime($this->createdTo . ' 23:59:59')]);
    }

    return $dataProvider;
  }
}
