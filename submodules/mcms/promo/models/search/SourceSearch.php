<?php

namespace mcms\promo\models\search;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\components\UsersHelper;
use mcms\promo\models\Domain;
use mcms\promo\models\SourceOperatorLanding;
use mcms\promo\models\Stream;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\Source;
use yii\db\Expression;

/**
 * SourceSearch represents the model behind the search form about `mcms\promo\models\Source`.
 */
class SourceSearch extends Source
{

  const MIN_LENGTH_SEARCH_FROM_BEGINING = 3;
  const SCENARIO_IDS_SEARCH = 'statistic_search';
  const SCENARIO_STAT_FILTERS = 'statistic_stat_filters';

  public $createdFrom;
  public $createdTo;
  public $link;
  public $queryName;

  /**
   * Нужно ли скрывать отклоненные источники и ссылки
   * @var mixed
   */
  public $hideInactive;

  /**
   * Нужно ли скрывать удаленные источники и ссылки
   * @var mixed
   */
  public $hideDeclined;

  // Необходимы для фильтра ссылок в партнерском кабинете
  public $stream_ids;
  public $user_ids;
  public $domain_ids;
  public $ads_type_ids;
  public $categories_ids;

  public $operatorLandingLinks;
  public $addPrelandOperators;
  public $offPrelandOperators;
  public $blockedOperators;

  /*
  * @var string
  */
  public $orderByFieldStatus;
  public $orderByStreamName = false;

  public function rules()
  {
    return [
      [['id'], 'integer', 'except' => [self::SCENARIO_IDS_SEARCH, self::SCENARIO_STAT_FILTERS]],
      [['id'], 'each', 'rule' => ['integer'], 'on' => self::SCENARIO_IDS_SEARCH],
      [['id'], 'each', 'rule' => ['integer'], 'on' => self::SCENARIO_STAT_FILTERS],
      [['user_id', 'default_profit_type', 'ads_type', 'status', 'stream_id', 'domain_id',
        'is_notify_subscribe', 'is_notify_rebill', 'is_notify_unsubscribe', 'is_notify_cpa', 'trafficback_type',
        'created_at', 'updated_at', 'category_id', 'operatorLandingLinks'], 'integer'],
      [['stream_ids', 'user_ids', 'domain_ids', 'ads_type_ids', 'categories_ids'], 'each', 'rule' => ['integer']],
      [['hash', 'url', 'name', 'postback_url', 'trafficback_url', 'createdFrom', 'createdTo',
        'link', 'queryName', 'hideInactive', 'hideDeclined', 'set_id',
        'addPrelandOperators', 'offPrelandOperators', 'blockedOperators', 'source_type'], 'safe'],
    ];
  }

  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_IDS_SEARCH => ['id'],
      self::SCENARIO_STAT_FILTERS => ['id', 'source_type', 'queryName']
    ]);
  }

  public function search($params)
  {
    $query = Source::find()->distinct();

    $query->with(['addPrelandOperators', 'offPrelandOperators', 'blockedOperators']);

    $query->joinWith([
      'stream',
      'sourceOperatorLanding',
      'sourceOperatorLanding.operator',
      'sourceOperatorLanding.operator.country',
      'sourceOperatorLanding.landing',
    ]);
//id url hash, category,status, ads_type, created_at
    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => ['created_at' => SORT_DESC],
        'attributes' => [
          'id',
          'url',
          'hash',
          'category_id',
          'ads_type',
          'name',
          'stream.name' => [
            'asc' => ['streams.name' => SORT_ASC],
            'desc' => ['streams.name' => SORT_DESC],
          ],
          'status',
          'created_at',
          'add_operator_preland' => false,
        ]
      ]
    ]);
    if ($this->scenario !== self::SCENARIO_STAT_FILTERS) {
      $this->load($params);
    }

    if (!$this->validate()) {
      return $dataProvider;
    }

    $query->andFilterWhere([
      self::tableName() . '.id' => $this->id,
      self::tableName() . '.default_profit_type' => $this->default_profit_type,
      self::tableName() . '.ads_type' => $this->ads_type,
      self::tableName() . '.status' => $this->status,
      self::tableName() . '.category_id' => $this->category_id,
      self::tableName() . '.source_type' => $this->source_type,
      self::tableName() . '.stream_id' => $this->stream_id,
      self::tableName() . '.domain_id' => $this->domain_id,
      self::tableName() . '.is_notify_subscribe' => $this->is_notify_subscribe,
      self::tableName() . '.is_notify_rebill' => $this->is_notify_rebill,
      self::tableName() . '.is_notify_unsubscribe' => $this->is_notify_unsubscribe,
      self::tableName() . '.is_notify_cpa' => $this->is_notify_cpa,
      self::tableName() . '.trafficback_type' => $this->trafficback_type,
      self::tableName() . '.set_id' => $this->set_id,
    ]);

    $query->andFilterWhere([self::tableName() . '.stream_id' => $this->stream_ids]);
    $query->andFilterWhere([self::tableName() . '.user_id' => $this->user_ids]);
    $query->andFilterWhere([self::tableName() . '.domain_id' => $this->domain_ids]);
    $query->andFilterWhere([self::tableName() . '.ads_type' => $this->ads_type_ids]);
    $query->andFilterWhere([self::tableName() . '.category_id' => $this->categories_ids]);

    $query->andFilterWhere(['like', self::tableName() . '.hash', $this->hash])
      ->andFilterWhere(['like', self::tableName() . '.url', $this->url])
      ->andFilterWhere(['like', self::tableName() . '.name', $this->name])
      ->andFilterWhere(['like', self::tableName() . '.postback_url', $this->postback_url])
      ->andFilterWhere(['like', self::tableName() . '.trafficback_url', $this->trafficback_url]);

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.created_at', strtotime($this->createdFrom)]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<=', self::tableName() . '.created_at', strtotime($this->createdTo . ' 23:59:59')]);
    }
    if ($this->hideInactive) {
      $query->andFilterWhere(['!=', self::tableName() . '.status', Source::STATUS_INACTIVE]);
    }
    if ($this->hideDeclined) {
      $query->andFilterWhere(['!=', self::tableName() . '.status', Source::STATUS_DECLINED]);
    }

    if($this->link) {
      $query->leftJoin(Domain::tableName(), self::tableName() . '.domain_id = '. Domain::tableName() . '.id');
      preg_match('/\?hash=([0-9a-z]+)/i', $this->link, $hash);
      empty($hash) && preg_match('/\/([0-9a-z]+)\//i', $this->link, $hash);

      $where = ['or'];
      if(!empty($hash)) {
        $where[] = ['like', self::tableName() . '.hash',  ArrayHelper::getValue($hash, 1)];
      } else {
        $where[] = ['like', self::tableName() . '.hash', $this->link];
      }
      $where[] = ['like', Domain::tableName() . '.url', $this->link];

      $query->andFilterWhere($where);
    }

    $notAvailableUserIds = UsersHelper::getCurrentUserNotAvailableUsers();
    if (count($notAvailableUserIds) > 0) {
      $query->andFilterWhere(['not in', self::tableName() . '.user_id', $notAvailableUserIds]);
    }

    if ($this->operatorLandingLinks) {
      $query->andFilterWhere([SourceOperatorLanding::tableName() . '.landing_id' => $this->operatorLandingLinks]);
    }

    if ($this->blockedOperators) {
      $query->leftJoin(Source::LINK_BLOCKED_OPERATORS_TABLE,
        self::tableName() . '.id = '. Source::LINK_BLOCKED_OPERATORS_TABLE . '.source_id');
      $query->andFilterWhere([Source::LINK_BLOCKED_OPERATORS_TABLE . '.operator_id' => $this->blockedOperators]);
    }

    if ($this->addPrelandOperators) {
      $query->leftJoin(Source::LINK_ADD_PRELAND_OPERATORS_TABLE,
        self::tableName() . '.id = '. Source::LINK_ADD_PRELAND_OPERATORS_TABLE . '.source_id');
      $query->andFilterWhere([Source::LINK_ADD_PRELAND_OPERATORS_TABLE . '.operator_id' => $this->addPrelandOperators]);
    }

    if ($this->offPrelandOperators) {
      $query->leftJoin(Source::LINK_OFF_PRELAND_OPERATORS_TABLE,
        self::tableName() . '.id = '. Source::LINK_OFF_PRELAND_OPERATORS_TABLE . '.source_id');
      $query->andFilterWhere([Source::LINK_OFF_PRELAND_OPERATORS_TABLE . '.operator_id' => $this->offPrelandOperators]);
    }

    if ($this->queryName !== null) {

      $nameQueryParam = $this->queryName;
      $nameQueryParamEscaped = true;

      if (mb_strlen($this->queryName) <= self::MIN_LENGTH_SEARCH_FROM_BEGINING) {
        $nameQueryParam = $this->queryName;
        $nameQueryParamEscaped = false;
      }

      $queryPart = null;
      if ($this->scenario === self::SCENARIO_STAT_FILTERS) {
        $queryPart = ['or',
          ($nameQueryParamEscaped === false
            ? ['like', self::tableName() . '.' . 'name', $nameQueryParam]
            : ['like', self::tableName() . '.' . 'name', $nameQueryParam]
          ),
          ['like', self::tableName() . '.' . 'id', $this->queryName],
        ];
      }
      else {
        $queryPart = ['or',
          ($nameQueryParamEscaped === false
            ? ['like', self::tableName() . '.' . 'name', $nameQueryParam, $nameQueryParamEscaped]
            : ['like', self::tableName() . '.' . 'name', $nameQueryParam]
          ),
          [
            'and',
            ['like', self::tableName() . '.' . 'id' ,  $this->queryName],
            ['!=', self::tableName() . '.' . 'id' ,  $this->queryName],
          ],
        ];
      }

      $queryPart !== null && $query->andWhere($queryPart);
    }

    Yii::$app->user->can('PromoViewOtherPeopleSources')
      ? $query->andFilterWhere([self::tableName() . '.user_id' => $this->user_id])
      : $query->andFilterWhere([self::tableName() . '.user_id' => Yii::$app->user->id]);

    if ($this->orderByFieldStatus) {
      $query->orderBy([new Expression('FIELD (' . self::tableName() . '.status, ' . $this->orderByFieldStatus . ') DESC')]);
    }

    if ($this->orderByStreamName) {
      $query->select(self::tableName(). '.*, ' . Stream::tableName().'.name as stream');
      $query->orderBy([new Expression('stream ASC')]);
    }

    // Скрытие элементов недоступных пользователей
    Yii::$app->user->identity->filterUsersItems($query, $this, 'user_id');

    return $dataProvider;
  }
}
