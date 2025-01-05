<?php

namespace mcms\statistic\models\mysql;

use mcms\common\helpers\ArrayHelper;
use mcms\common\multilang\LangAttribute;
use mcms\statistic\components\AbstractStatistic;
use mcms\statistic\components\StatisticQuery;
use mcms\user\Module;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class Banners
 * @package mcms\statistic\models\mysql
 */
class Banners extends AbstractStatistic
{

  const STATISTIC_NAME = 'banners';
  const TABLE = 'banners_day_group';
  public $group = 'date';

  public $banners;
  public $operators;
  public $platforms;
  public $countries;
  public $users;
  public $sources;
  public $isFake;

  /** @var  array кэш для хранения Итого */
  private $_results;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return array_merge(parent::rules(), [
      [['operators', 'platforms', 'countries', 'banners', 'users', 'sources', 'isFake'], 'safe'],
    ]);
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'start_date' => Yii::_t('statistic.statistic.start_date'),
      'end_date' => Yii::_t('statistic.statistic.end_date'),
      'sources' => Yii::_t('statistic.statistic.sources'),
      'countries' => Yii::_t('statistic.statistic.countries'),
      'banners' => Yii::_t('statistic.statistic.banners'),
      'operators' => Yii::_t('statistic.statistic.operators'),
      'platforms' => Yii::_t('statistic.statistic.platforms'),
      'users' => Yii::_t('statistic.statistic.users'),
      'date' => Yii::_t('statistic.statistic.date'),
      'isFake' => Yii::_t('statistic.statistic.isFake')
    ];
  }

  /**
   * Перевод для колонок грида
   * @return array
   */
  public function gridColumnLabels()
  {
    return array_filter([
      'banner_id' => Yii::_t('statistic.statistic.banners'),
      'count_hits' => Yii::_t('statistic.statistic.count_hits'),
      'count_ons' => Yii::_t('statistic.statistic.count_ons'),
      'count_onetimes' => Yii::_t('statistic.statistic.count_onetime'),
      'count_solds' => Yii::_t('statistic.statistic.count_sold'),
      'count_shows' => Yii::_t('statistic.statistic.count_shows'),
      'ctr' => Yii::_t('statistic.statistic.ctr'),
      'cr' => Yii::_t('statistic.statistic.cr'),
    ]);
  }

  /**
   * Получение сгруппированной статистики
   * @return ActiveDataProvider
   */
  public function getStatisticGroup()
  {
    $this->handleOpenCloseFilters();

    $query = $this->getQuery($this->getSelectFields());

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => ['banner_id' => SORT_DESC],
        'attributes' => [
          'banner_id',
          'count_shows',
          'count_hits',
          'count_ons',
          'count_onetimes',
          'count_solds',
          'cr',
          'ctr'
        ]
      ],
    ]);

    return $dataProvider;
  }

  /**
   * @inheritdoc
   */
  public function handleFilters(Query &$query)
  {
    /** @var Module $usersModule */
    $usersModule = Yii::$app->getModule('users');


    /** @var $query StatisticQuery */
    $query
      ->andFilterWhere(['>=', 'st.date', $this->formatDateDB($this->start_date)])
      ->andFilterWhere(['<=', 'st.date', $this->formatDateDB($this->end_date)])
    ;

    if ($this->canFilterByOperators()) {
      $query->andFilterWhere(['st.operator_id' => $this->operators]);
    }

    if ($this->canFilterBySources()) {
      $sourceIds = array_merge(
        (empty($this->sources) ? [] : (is_array($this->sources) ? $this->sources : [$this->sources]))
      );
      $query->andFilterWhere(['st.source_id' => $sourceIds]);
    }

    if ($this->canFilterByPlatform()) {
      $query->andFilterWhere(['st.platform_id' => $this->platforms]);
    }

    if ($this->canFilterByCountries()) {
      $query->andFilterWhere(['st.country_id' => $this->countries]);
    }

    if ($this->canFilterByBanners()) {
      $query->andFilterWhere(['st.banner_id' => $this->banners]);
    }

    if ($this->canFilterByUsers()) {
      $query->andFilterWhere(['st.user_id' => $this->users]);

      $notAvailableUserIds = $usersModule
        ->api('notAvailableUserIds', [
          'userId' => Yii::$app->user->id,
        ])
        ->getResult();

      if ($notAvailableUserIds) {
        $query->andWhere(['not in', 'st.user_id', $notAvailableUserIds]);
      }
    } else {
      $query->andWhere(['st.user_id' => Yii::$app->user->id]);
    }

    if ($this->canFilterByFakeRevshare()) {
      $query->andFilterWhere(['st.is_fake' => $this->isFake]);
    }

    if (!$this->canViewHiddenSoldSubscriptions() || !$this->canViewHiddenOnetimeSubscriptions()) {
      $query->andFilterWhere(['st.is_visible_to_partner' => 1]);
    }

  }

  /**
   * @inheritdoc
   */
  public function getFilterFields()
  {
    return [
      'operators',
      'platforms',
      'countries',
      'banners',
      'users',
      'sources',
    ];
  }

  /**
   * @param array $row
   * @return string
   */
  public function formatBannerName(array $row)
  {
    $name = new LangAttribute($row['bannerName']);

    $templateName = new LangAttribute($row['templateName']);

    return sprintf('#%s %s (%s)',
      $row['banner_id'],
      $name,
      $templateName
    );
  }

  /**
   * @return array
   */
  public function getSelectFields()
  {
    return [
      'banner_id' => 'banner_id',
      'bannerName' => 'b.name',
      'templateName' => 'bt.name',
      'count_shows' => 'SUM(st.count_shows)',
      'count_hits' => 'SUM(st.count_hits)',
      'count_ons' => 'SUM(st.count_ons)',
      'count_onetimes' => 'SUM(st.count_onetimes)',
      'count_solds' => 'SUM(st.count_solds)',
      'cr' => new Expression('IF(
          SUM(st.count_hits) = 0, 
          NULL, 
          (SUM(st.count_ons) + SUM(st.count_onetimes) + SUM(st.count_solds)) * 100 / SUM(st.count_hits)
        )'),
      'ctr' => new Expression('IF(
          SUM(st.count_shows) = 0, 
          NULL, 
          SUM(st.count_hits) * 100 / SUM(st.count_shows)
        )'),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getQuery(array $select = [])
  {

    $q = (new StatisticQuery())
      ->setId(self::STATISTIC_NAME)
      ->select($select)
      ->from(self::TABLE . ' st')
      ->groupBy('banner_id')
      ->leftJoin('banners b', 'b.id = st.banner_id')
      ->leftJoin('banner_templates bt', 'bt.id = b.template_id');

    $this->handleFilters($q);

    return $q;
  }


  /**
   * @return array|bool получить строку ИТОГО
   */
  private function getResults()
  {
    if ($this->_results) return $this->_results;
    $subQuery = '(' . $this->getQuery($this->getSelectFields())->createCommand()->getRawSql() . ')';
    $subQueryAlias = 'rows';
    $subQuerySelects = $this->getSelectFields();

    $querySelects = [];
    foreach ($subQuerySelects AS $fieldName => $expression) {

      switch ($fieldName) {
        case 'count_shows':
          $querySelects[$fieldName] = new Expression(
            'SUM(rows.count_shows)'
          );
          break;
        case 'count_hits':
          $querySelects[$fieldName] = new Expression(
            'SUM(rows.count_hits)'
          );
          break;
        case 'ctr':
          $querySelects[$fieldName] = new Expression(
            'IF(
              SUM(rows.count_shows) = 0, 
              NULL, 
              SUM(rows.count_hits) * 100 / SUM(rows.count_shows)
            )'
          );
          break;
        case 'count_ons':
          $querySelects[$fieldName] = new Expression(
            '(SUM(rows.count_ons))'
          );
          break;
        case 'count_onetimes':
          $querySelects[$fieldName] = new Expression(
            '(SUM(rows.count_onetimes))'
          );
          break;
        case 'count_solds':
          $querySelects[$fieldName] = new Expression(
            '(SUM(rows.count_solds))'
          );
          break;
        case 'cr':
          $querySelects[$fieldName] = new Expression(
            'IF(
              SUM(rows.count_hits) = 0, 
              NULL, 
              (SUM(rows.count_ons) + SUM(rows.count_onetimes) + SUM(rows.count_solds)) * 100 / SUM(rows.count_hits)
            )'
          );
          break;
      }
    }

    $query = (new Query())
      ->select($querySelects)
      ->from([$subQueryAlias => $subQuery]);

    return $this->_results = $query->one();
  }

  /**
   * @param $field
   * @return mixed
   */
  public function getResultValue($field)
  {
    return ArrayHelper::getValue($this->getResults(), $field);
  }

}