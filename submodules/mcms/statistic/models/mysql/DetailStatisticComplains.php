<?php

namespace mcms\statistic\models\mysql;

use mcms\statistic\components\AbstractDetailStatistic;
use mcms\statistic\components\StatisticQuery;
use mcms\statistic\models\Complain;
use mcms\statistic\Module;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

class DetailStatisticComplains extends AbstractDetailStatistic
{
  const GROUP_NAME = 'complains';
  const STATISTIC_NAME = 'complains';

  public $landings;
  public $sources;
  public $operators;
  public $platforms;
  public $streams;
  public $providers;
  public $countries;
  public $users;
  public $landing_pay_types;
  public $is_visible_to_partner;
  public $hit_id;
  public $phone_number;
  public $description;
  public $type;
  public $referer;
  /** @var Module $statisticModule */
  public $statisticModule;

  public function init()
  {
    parent::init();
    $this->group = self::GROUP_NAME;

    // С формы может прийти пустая строка, из-за чего происходит ошибка
    if (empty($this->streams)) $this->streams = null;

    $this->statisticModule = Yii::$app->getModule('statistic');
  }

  public static function tableName()
  {
    return 'complains';
  }

  public function rules()
  {
    return array_merge([
      [['hit_id', 'phone_number', 'is_visible_to_partner'], 'integer'],
      [['landings', 'sources', 'operators', 'platforms', 'streams', 'providers', 'countries', 'users', 'landing_pay_types', 'type', 'revshareOrCPA', 'referer'], 'safe']
    ], parent::rules());
  }

  /**
   * Перевод для колонок грида
   * @return array
   */
  public function gridColumnLabels()
  {
    return $this->attributeLabels();
  }

  public function handleFilters(Query &$query)
  {
    if (in_array($this->revshareOrCPA, [self::REVSHARE, self::CPA])) {
      $query->leftJoin('hits', 'st.hit_id = hits.id');
    }

    if ($this->revshareOrCPA === self::REVSHARE) {
      $query->andWhere(['hits.is_cpa' => 0]);
    }

    if ($this->revshareOrCPA === self::CPA) {
      $query->andWhere(['hits.is_cpa' => 1]);
    }

    if ($this->start_date) {
      /** @var Module $module */
      $module = Yii::$app->getModule('statistic');
      $canViewFullTime = $module->canViewFullTimeStatistic();
      if (!$canViewFullTime) {
        $time = strtotime($this->start_date);
        $minTime = strtotime('-3 months');

        $time < $minTime && $this->start_date = date('Y-m-d', $minTime);
      }

      $query->andFilterWhere(['>=', 'st.date', $this->formatDateDB($this->start_date)]);
    }

    if ($this->end_date) {
      $query->andFilterWhere(['<=', 'st.date', $this->formatDateDB($this->end_date)]);
    }

    $query->andFilterWhere(['like', 'st.phone' , $this->phone_number]);
    $query->andFilterWhere(['like', 'st.description' , $this->description]);

    if ($this->canFilterByLandings()) {
      $query->andFilterWhere(['st.landing_id' => $this->landings]);
    }

    if ($this->canFilterByOperators()) {
      $query->andFilterWhere(['st.operator_id' => $this->operators]);
    }

    if ($this->canFilterBySources()) {
      $query->andFilterWhere(['st.source_id' => $this->sources]);
    }

    if ($this->canFilterByPlatform()) {
      $query->andFilterWhere(['st.platform_id' => $this->platforms]);
    }

    if ($this->canFilterByStreams()) {
      $query->andFilterWhere(['st.stream_id' => $this->streams]);
    }

    if ($this->canFilterByCountries()) {
      $query->andFilterWhere(['st.country_id' => $this->countries]);
    }

    if ($this->canFilterByUsers()) {
      $query->andFilterWhere(['st.user_id' => $this->users]);
      Yii::$app->user->identity->filterUsersItems($query, 'st', 'user_id');
    } else {
      // показываем только проданные данным юзером подписки
      $query->andWhere(['st.user_id' => Yii::$app->user->id]);
    }

    $query->andFilterWhere(['like', 'hp.referer' , $this->referer]);

    if ($this->canFilterByProviders()) {
      $query->andFilterWhere(['st.provider_id' => $this->providers]);
    }

    if ($this->canFilterByLandingPayTypes()) {
      $query->andFilterWhere(['st.landing_pay_type_id' => $this->landing_pay_types]);
    }

    $query->andFilterWhere(['st.hit_id' => $this->hit_id]);
    $query->andFilterWhere(['st.type' => $this->type]);

    if ($this->canFilterByCurrency()) {
      $query->leftJoin(
        'landing_operators',
        'landing_operators.landing_id = st.landing_id and landing_operators.operator_id = st.operator_id'
      )->andWhere(['default_currency_id' => $this->allCurrencies[$this->currency]]);
    }

    if ($this->is_visible_to_partner !== null && $this->is_visible_to_partner !== '') {
      $query->leftJoin('sold_subscriptions ss', 'ss.hit_id=st.hit_id');
      $query->leftJoin('onetime_subscriptions os', 'os.hit_id=st.hit_id');
      $query->andFilterWhere([
        'OR',
        ['ss.is_visible_to_partner' => $this->is_visible_to_partner],
        ['os.is_visible_to_partner' => $this->is_visible_to_partner]
      ]);
    }

    //TRICKY Отображаем в стате в ПП жалобы в соответствии с настройками модуля
    if (Yii::$app->user->identity->hasRole('partner')) {
      $complainTypes = ['or', new Expression('0 = 1')];
      if ($this->statisticModule->canPartnerViewComplainText()) {
        $complainTypes[] = ['st.type' => Complain::TYPE_TEXT];
      }
      if ($this->statisticModule->canPartnerViewComplainCall()) {
        $complainTypes[] = ['st.type' => Complain::TYPE_CALL];
      }
      if ($this->statisticModule->canPartnerViewComplainAuto24()) {
        $complainTypes[] = ['st.type' => Complain::TYPE_AUTO_24];
      }
      if ($this->statisticModule->canPartnerViewComplainAutoMoment()) {
        $complainTypes[] = ['st.type' => Complain::TYPE_AUTO_MOMENT];
      }
      if ($this->statisticModule->canPartnerViewComplainAutoDuplicate()) {
        $complainTypes[] = ['st.type' => Complain::TYPE_AUTO_DUPLICATE];
      }
      if ($this->statisticModule->canPartnerViewComplainCallMno()) {
        $complainTypes[] = ['st.type' => Complain::TYPE_CALL_MNO];
      }
      $query->andWhere($complainTypes);

      $query->addSelect([
        'hp.referer',
        'hp.user_agent',
      ]);
    }
  }

  public function addQueryJoins(Query $query)
  {
    $query->innerJoin('hit_params hp', 'hp.hit_id = st.hit_id');
    $query->addSelect(['hp.ip', 'hp.referer AS referrer', 'hp.user_agent AS userAgent']);
    return parent::addQueryJoins($query);
  }

  /**
   * Возвращает два сформированных запроса [query] для сгруппированной статистики
   * @return StatisticQuery[]
   */
  public function getStatisticGroupQueries()
  {
    $this->handleOpenCloseFilters();

    $query = (new StatisticQuery())
      ->select([
        'hit_id' => 'st.hit_id',
        'user_id' => 'st.user_id',
        'time' => 'st.time',
        'phone_number' => 'st.phone',
        'description' => 'st.description',
        'email' => 'u.email',
        'type' => 'st.type',
      ])->from(['st' => self::tableName()]);

    $query = $this->addQueryJoins($query);
    
    if (isset($this->requestData['sort'])) {
      $sortParam = $this->requestData['sort'];
      $order = strncmp($sortParam, '-', 1) === 0 ? SORT_DESC : SORT_ASC;
      $sortAttr = $order === SORT_DESC ? substr($sortParam, 1) : $sortParam;
      if (in_array($sortAttr, $this->getSortAttributes())) {
        $query->orderBy([$sortAttr => $order]);
      }
    } else {
      $query->orderBy(['time' => SORT_DESC]);
    }

    $this->handleFilters($query);

    return [$query];
  }

  public function getStatisticGroup()
  {
    list($query) = $this->getStatisticGroupQueries();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'attributes' => $this->getSortAttributes()
      ],
    ]);

    return $dataProvider;
  }

  /**
   * Атрибуты для сортировки
   * @return array
   */
  private function getSortAttributes()
  {
    return [
      'hit_id',
      'time',
      'description',
      'phone_number',
      'country.name' => 'country_name',
      'source.name' => 'source_name',
      'operator.name' => 'operator_name',
      'stream.name' => 'stream_name',
      'platform.name' => 'platform_name',
      'l.name' => 'landing_name',
      'ip',
      'pt.name' => 'landing_pay_type_name',
      'u.email' => 'email',
      'type',
    ];
  }

  function findOne($recordId)
  {
    $query = (new StatisticQuery())
      ->select([
        'hit_id' => 'st.hit_id',
        'time' => 'st.time',
        'description' => 'st.description',
        'phone_number' => 'st.phone',
        'email' => 'u.email',
        'user_id' => 'st.user_id',
        'type' => 'st.type'
      ])
      ->from(['st' => self::tableName()])
      ->where(['st.hit_id' => $recordId]);


    $query = $this->addQueryJoins($query);

    $query->leftJoin('search_subscriptions ssb', 'ssb.hit_id = st.hit_id');
    $query->leftJoin('onetime_subscriptions os', 'os.hit_id = st.hit_id');
    $query->addSelect([
      'IF(ssb.hit_id IS NOT NULL, ssb.time_on, os.time) AS subscribed_at',
      'ssb.time_off AS unsubscribed_at',
      'ssb.time_rebill AS rebilled_at'
    ]);

    return $query->one();
  }

  public function getFilterFields()
  {
    return [
      'landings',
      'sources',
      'operators',
      'platforms',
      'streams',
      'providers',
      'countries',
      'hit_id',
      'phone_number',
      'users',
      'landing_pay_types',
      'is_visible_to_partner',
    ];
  }

  /**
   * TRICKY нужно перенести в getGridColumns
   * @return array
   */
  public function getExportAttributeLabels()
  {
    return [
      'ip' => Yii::_t('statistic.statistic.detail-ip'),
      'description' => Yii::_t('statistic.statistic.complain_description'),
      'stream_name' => Yii::_t('statistic.statistic.detail-stream-name'),
      'url' => Yii::_t('statistic.statistic.detail-url'),
      'source_name' => Yii::_t('statistic.statistic.sources'),
      'platform_name' => Yii::_t('statistic.statistic.platforms'),
      'landing_name' => Yii::_t('statistic.statistic.landings'),
      'operator_name' => Yii::_t('statistic.statistic.operators'),
      'country_name' => Yii::_t('statistic.statistic.countries'),
      'landing_pay_types' => Yii::_t('statistic.statistic.landing_pay_types'),
      'providers' => Yii::_t('statistic.statistic.providers'),
      'landing_pay_type_name' => Yii::_t('statistic.statistic.landing_pay_type_name'),
      'phone' => Yii::_t('statistic.statistic.detail-phone_number'),
      'user_id' => Yii::_t('statistic.statistic.users'),
      'referrer' => Yii::_t('statistic.statistic.referrer'),
      'userAgent' => Yii::_t('statistic.statistic.userAgent'),
      'hit_id' => Yii::_t('statistic.statistic.detail-transition_id'),
      'phone_number' => Yii::_t('statistic.statistic.detail-phone_number'),
      'time' => Yii::_t('statistic.statistic.date'),
      'email' => Yii::_t('statistic.statistic.email'),
      'type' => Yii::_t('statistic.statistic.complain_type'),
      'subscribed_at' => Yii::_t('statistic.statistic.subscribed_at'),
      'unsubscribed_at' => Yii::_t('statistic.statistic.unsubscribed_at'),
      'rebilled_at' => Yii::_t('statistic.statistic.rebilled_at'),
    ];
  }

  public function attributeLabels()
  {
    return [
      'ip' => Yii::_t('statistic.statistic.detail-ip'),
      'description' => Yii::_t('statistic.statistic.complain_description'),
      'stream' => Yii::_t('statistic.statistic.detail-stream-name'),
      'url' => Yii::_t('statistic.statistic.detail-url'),
      'source' => Yii::_t('statistic.statistic.sources'),
      'platforms' => Yii::_t('statistic.statistic.platforms'),
      'landings' => Yii::_t('statistic.statistic.landings'),
      'operators' => Yii::_t('statistic.statistic.operators'),
      'countries' => Yii::_t('statistic.statistic.countries'),
      'landing_pay_types' => Yii::_t('statistic.statistic.landing_pay_types'),
      'is_visible_to_partner' => Yii::_t('statistic.statistic.sold_is_visible_to_partner'),
      'providers' => Yii::_t('statistic.statistic.providers'),
      'landing_pay_type_name' => Yii::_t('statistic.statistic.landing_pay_type_name'),
      'phone' => Yii::_t('statistic.statistic.detail-phone_number'),
      'user_id' => Yii::_t('statistic.statistic.users'),
      'referrer' => Yii::_t('statistic.statistic.referrer'),
      'userAgent' => Yii::_t('statistic.statistic.userAgent'),
      'hit_id' => Yii::_t('statistic.statistic.detail-transition_id'),
      'phone_number' => Yii::_t('statistic.statistic.detail-phone_number'),
      'time' => Yii::_t('statistic.statistic.date'),
      'email' => Yii::_t('statistic.statistic.email'),
      'type' => Yii::_t('statistic.statistic.complain_type'),
      'start_date' => Yii::_t('statistic.statistic.start_date'),
      'end_date' => Yii::_t('statistic.statistic.end_date'),
      'subscribed_at' => Yii::_t('statistic.statistic.subscribed_at'),
      'unsubscribed_at' => Yii::_t('statistic.statistic.unsubscribed_at'),
      'rebilled_at' => Yii::_t('statistic.statistic.rebilled_at'),
    ];
  }

  /**
   * @inheritdoc
   */
  protected function isCpaVisible($gridRow){}
}