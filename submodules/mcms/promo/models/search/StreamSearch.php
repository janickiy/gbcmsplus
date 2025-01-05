<?php

namespace mcms\promo\models\search;

use mcms\promo\components\UsersHelper;
use mcms\promo\models\Source;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\Stream;

/**
 * StreamSearch represents the model behind the search form about `mcms\promo\models\Stream`.
 */
class StreamSearch extends Stream
{

  const MIN_LENGTH_SEARCH_FROM_BEGINING = 3;
  const SCENARIO_ADMIN = 'admin';
  const SCENARIO_IDS_SEARCH = 'statistic_search';
  const SCENARIO_STAT_FILTERS = 'statistic_stat_filters';
  public $queryName;
  public $createdFrom;
  public $createdTo;
  //Фильтрация по юзерам
  public $user_ids;
  public $source_ids;

  public function rules()
  {
    return [
      [['id'], 'integer', 'except' => [self::SCENARIO_IDS_SEARCH, self::SCENARIO_STAT_FILTERS]],
      [['id'], 'each', 'rule' => ['integer'], 'on' => [self::SCENARIO_IDS_SEARCH, self::SCENARIO_STAT_FILTERS]],
      [['status', 'user_id', 'created_at', 'updated_at'], 'integer'],
      [['queryName'], 'string'],
      [['name', 'createdFrom', 'createdTo'], 'safe'],
      [['user_ids', 'source_ids'], 'each', 'rule' => ['integer']],
    ];
  }

  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_ADMIN => ['id', 'name', 'user_id', 'created_at', 'status', 'createdFrom', 'createdTo'],
      self::SCENARIO_IDS_SEARCH => ['id'],
      self::SCENARIO_STAT_FILTERS => ['id', 'queryName'],
    ]);
  }

  public function search($params)
  {
    $query = Stream::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'id' => SORT_DESC,
        ]
      ],
    ]);

    if (!in_array($this->scenario, [self::SCENARIO_ADMIN, self::SCENARIO_STAT_FILTERS])) {
      $this->status = self::STATUS_ACTIVE;
    }
    $this->load($params);

    if (!$this->validate()) {
      return $dataProvider;
    }

    $query->andFilterWhere([
      self::tableName() . '.id' => $this->id,
      self::tableName() . '.status' => $this->status,
      self::tableName() . '.user_id' => $this->user_id,
      self::tableName() . '.created_at' => $this->created_at,
      self::tableName() . '.updated_at' => $this->updated_at,
    ]);

    $query->andFilterWhere(['like', self::tableName() . '.name', $this->name]);

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.created_at', strtotime($this->createdFrom)]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<=', self::tableName() . '.created_at', strtotime($this->createdTo . ' 23:59:59')]);
    }

    $notAvailableUserIds = UsersHelper::getCurrentUserNotAvailableUsers();
    if (count($notAvailableUserIds) > 0) {
      $query->andFilterWhere(['not in', self::tableName() . '.' . 'user_id', $notAvailableUserIds]);
    }

    if (!Yii::$app->user->can('PromoViewOtherPeopleStreams')) {
      $query->andFilterWhere([self::tableName() . '.' . 'user_id' => Yii::$app->user->id]);
    }

    if ($this->queryName) {
      if ($this->scenario !== self::SCENARIO_STAT_FILTERS) {
        $query
          ->andWhere(['!=', self::tableName() . '.' . 'id' ,  $this->queryName])
        ;
      }
      $query
        ->andWhere([
          'or',
          mb_strlen($this->queryName) > self::MIN_LENGTH_SEARCH_FROM_BEGINING
            ? ['like', self::tableName() . '.' . 'name' ,  $this->queryName]
            : ['like', self::tableName() . '.' . 'name' ,  $this->queryName . '%', false]
          ,
          ['like', self::tableName() . '.' . 'id' ,  $this->queryName],
        ]);
    }

    if ($this->user_ids) {
      $query->andFilterWhere([self::tableName() . '.user_id' => $this->user_ids]);
    }

    if ($this->source_ids) {
      $query->leftJoin(Source::tableName(), Source::tableName() . '.stream_id=' . self::tableName() . '.id');
      $query->andFilterWhere([Source::tableName() . '.id' => $this->source_ids]);
    }

    // Скрытие элементов недоступных пользователей
    Yii::$app->user->identity->filterUsersItems($query, $this, 'user_id');

    return $dataProvider;
  }
}