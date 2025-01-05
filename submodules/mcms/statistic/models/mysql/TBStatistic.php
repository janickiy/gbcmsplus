<?php
namespace mcms\statistic\models\mysql;


use mcms\statistic\components\AbstractStatistic;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use mcms\common\module\api\join\Query as JoinQuery;
use mcms\statistic\Module;
use mcms\common\helpers\ArrayHelper;


class TBStatistic extends AbstractStatistic
{

  public $landings;
  public $operators;
  public $platforms;
  public $streams;
  public $countries;
  public $webmasterSources;
  public $arbitraryLinks;

  private $statFilters;

  public function init()
  {
    parent::init();
  }

  public function rules()
  {
    return array_merge(parent::rules(), [
      [['landings', 'operators', 'platforms', 'streams', 'countries', 'webmasterSources', 'arbitraryLinks'], 'safe']
    ]);
  }

  /**
   * Перевод для колонок грида
   * @return array
   */
  public function gridColumnLabels()
  {
    return array_filter([
      'time' => 'Hit time',
      'ip' => 'IP',
      'country' => 'Country',
      'user_agent' => 'User-Agent',
      'referer' => 'Referer',
      'tb_reason' => 'Reason',
//      'platform' => 'Platform',
//      'source' => 'Link',
//      'source_id' => 'Link ID'
    ]);
  }

  public function getFilterFields()
  {
    return [
      'links',
      'streams',
      'webmasterSources',
      'arbitraryLinks',
      'countries',
      'operators',
      'platforms',
      'landings',
    ];
  }

  public function attributeLabels()
  {
    return [
//      'group' => Yii::_t('statistic.statistic.group'),
      'start_date' => Yii::_t('statistic.statistic.start_date'),
      'end_date' => Yii::_t('statistic.statistic.end_date'),
//      'streams' => Yii::_t('statistic.statistic.streams'),
//      'sources' => Yii::_t('statistic.statistic.sources'),
//      'landings' => Yii::_t('statistic.statistic.landings'),
//      'countries' => Yii::_t('statistic.statistic.countries'),
//      'operators' => Yii::_t('statistic.statistic.operators'),
//      'providers' => Yii::_t('statistic.statistic.providers'),
//      'platforms' => Yii::_t('statistic.statistic.platforms'),
//      'users' => Yii::_t('statistic.statistic.users'),
//      'landing_pay_types' => Yii::_t('statistic.statistic.landing_pay_types'),
//      'date' => Yii::_t('statistic.statistic.date'),
    ];
  }


  /**
   * Получение сгруппированной статистики
   * @return ActiveDataProvider
   */

  public function getStatisticGroup()
  {
    $this->handleOpenCloseFilters();

    // статистика по переходам
    $hitsQuery = (new Query())
      ->select([
        'hit_id' => 'st.id',
        'time' => 'st.time',
        'ip' => 'par.ip',
        'tb_reason' => 'st.is_tb',
        'user_agent' => 'par.user_agent',
        'referer' => 'par.referer',
      ])
      ->from('`hits` `st` USE INDEX(`hits_group_by_hour`)')
      ->leftJoin(['par' => 'hit_params'], 'st.id = par.hit_id')
    ;

    $this->handleFilters($hitsQuery);

    $this->addQueryJoins($hitsQuery);

    $dataProvider = new ActiveDataProvider([
      'query' => $hitsQuery,
      'sort' => [
        'defaultOrder' => ['time' => SORT_DESC],
        'attributes' => [
          'time'
        ]
      ],
      'pagination' => [
        'pageSize' => 10
      ]
    ]);

    return $dataProvider;

  }

  public function addQueryJoins(Query &$query)
  {
    $sourceApi = Yii::$app->getModule('promo')->api('source');
    $sourceQuery = new JoinQuery(
      $query,
      'st',
      ['INNER JOIN', 'st.source_id', '=', 'source'],
      [
        'source_name' => 'source.name',
        'source_url' => 'source.url',
        'source_type' => 'source.source_type',
        'source_id' => 'source.id'
      ]
    );
    $sourceApi->join($sourceQuery);


    $operatorApi = Yii::$app->getModule('promo')->api('operators');
    $operatorQuery = new JoinQuery(
      $query,
      'st',
      ['LEFT JOIN', 'st.operator_id', '=', 'operator'],
      [
        'operator_name' => 'operator.name',
        'operator_id' => 'operator.id'
      ]
    );
    $operatorApi->join($operatorQuery);

    $countryApi = Yii::$app->getModule('promo')->api('countries');
    $countryQuery = new JoinQuery(
      $query,
      'st',
      ['LEFT JOIN', 'operator.country_id', '=', 'country'],
      [
        'country_name' => 'country.name',
        'country_id' => 'country.id'
      ]
    );
    $countryApi->join($countryQuery);

    $landingApi = Yii::$app->getModule('promo')->api('landings');
    $landingQuery = new JoinQuery(
      $query,
      'st',
      ['LEFT JOIN', 'st.landing_id', '=', 'landing'],
      [
        'landing_name' => 'landing.name',
        'landing_id' => 'landing.id'
      ]
    );
    $landingApi->join($landingQuery);

    $streamApi = Yii::$app->getModule('promo')->api('streams');
    $streamQuery = new JoinQuery(
      $query,
      'st',
      ['LEFT JOIN', 'source.stream_id', '=', 'stream'],
      [
        'stream_name' => 'stream.name',
        'stream_id' => 'stream.id'
      ]
    );
    $streamApi->join($streamQuery);

    $platformApi = Yii::$app->getModule('promo')->api('platforms');
    $platformQuery = new JoinQuery(
      $query,
      'st',
      ['LEFT JOIN', 'st.platform_id', '=', 'platform'],
      [
        'platform_name' => 'platform.name',
        'platform_id' => 'platform.id'
      ]
    );
    $platformApi->join($platformQuery);
  }

  public function handleFilters(Query &$query)
  {

    $query
      ->andFilterWhere(['>=', 'st.date', $this->formatDateDB($this->start_date)])
      ->andFilterWhere(['<=', 'st.date', $this->formatDateDB($this->end_date)]);

    if ($this->canFilterByLandings()) {
      $query->andFilterWhere(['st.landing_id' => $this->landings]);
    }

    if ($this->canFilterByOperators()) {
      $query->andFilterWhere(['st.operator_id' => $this->operators]);
    }

    if ($this->canFilterBySources()) {
      $sourceIds = array_merge(
        (empty($this->webmasterSources) ? [] : $this->webmasterSources),
        (empty($this->arbitraryLinks) ? [] : $this->arbitraryLinks)
      );
      $query->andFilterWhere(['st.source_id' => $sourceIds]);
    }

    if ($this->canFilterByStreams()) {
      $query->andFilterWhere(['source.stream_id' => $this->streams]);
    }

    if ($this->canFilterByPlatform()) {
      $query->andFilterWhere(['st.platform_id' => $this->platforms]);
    }

    if ($this->canFilterByCountries()) {
      $query->andFilterWhere(['operator.country_id' => $this->countries]);
    }

    if (!$this->canFilterByUsers()) {
      $query->andWhere(['source.user_id' => Yii::$app->user->id]);
    }

    $query->andWhere('st.is_tb > 0');

  }

  public function getArbitraryLinksByStreams($pagination = ['pageSize' => 1000], $group = 'stream.name')
  {
    $module = Yii::$app->getModule('promo');
    $sourceType = $module->api('sources')->getTypeArbitraryLink();
    return $module
      ->api('sources', [
        'conditions' => [
          'source_type' => $sourceType,
          'id' => []
        ],
        'pagination' => $pagination,
        'statFiltersUser' => Yii::$app->user->id,
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name', $group])
      ->getResult()
      ;
  }
  public function getWebmasterSources($pagination = ['pageSize' => 1000])
  {
    $module = Yii::$app->getModule('promo');
    $sourceType = $module->api('sources')->getTypeWebmasterSource();
    return $module
      ->api('sources', [
        'conditions' => [
          'source_type' => $sourceType,
          'id' => []
        ],
        'pagination' => $pagination,
        'statFiltersUser' => Yii::$app->user->id,
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult()
      ;
  }
  public function getStreams()
  {
    return Yii::$app->getModule('promo')
      ->api('streams', [
        'conditions' => [
          'id' => []
        ],
        'pagination' => ['pageSize' => 0],
        'statFiltersUser' => Yii::$app->user->id,
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult()
      ;
  }
  public function getCountries($currency = null)
  {
    return Yii::$app->getModule('promo')
      ->api('countries', [
        'conditions' => [
          'id' => []
        ],
        'pagination' => ['pageSize' => 0],
        'statFiltersUser' => Yii::$app->user->id,
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult()
      ;
  }
  public function getLandingsByCountry()
  {
    return Yii::$app->getModule('promo')
      ->api('landingOperators', [
        'conditions' => [
          'onlyActiveCountries' => true,
          'landing_id' => []
        ],
        'statFiltersUser' => Yii::$app->user->id,
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams([ 'landing.id', 'landing.name', 'operator.country.name'])
      ->getResult()
      ;
  }
  public function getPlatforms()
  {
    return Yii::$app->getModule('promo')
      ->api('platforms', [
        'conditions' => [
          'id' => []
        ],
        'statFiltersUser' => Yii::$app->user->id,
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult()
      ;
  }
  public function getOperatorsByCountry()
  {
    return Yii::$app->getModule('promo')
      ->api('operators', [
        'conditions' => [
          'onlyActiveCountries' => true,
          'id' => []
        ],
        'statFiltersUser' => Yii::$app->user->id,
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name', 'country.name'])
      ->getResult()
      ;
  }


}