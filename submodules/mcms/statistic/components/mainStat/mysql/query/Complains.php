<?php

namespace mcms\statistic\components\mainStat\mysql\query;

use mcms\common\helpers\Currency;
use mcms\promo\models\Source;
use mcms\statistic\components\mainStat\FormModel;
use mcms\statistic\components\mainStat\Group;
use mcms\statistic\models\Complain;
use mcms\user\models\User as UserModel;

/**
 * Достаем инфу по жалобам hits_day_group, hits_day_hour_group
 */
class Complains extends BaseQuery
{

  /**
   * @inheritdoc
   */
  public function init()
  {
    parent::init();

    $this
      ->select([
        'complains' => 'SUM(IF(st.type = :complainText, 1, 0))',
        'calls' => 'SUM(IF(st.type = :complainCall, 1, 0))',
        'callsMno' => 'SUM(IF(st.type = :complainCallMno, 1, 0))',
        'complainAuto24' => 'SUM(IF(st.type = :complainAuto24, 1, 0))',
        'complainAutoMoment' => 'SUM(IF(st.type = :complainAutoMoment, 1, 0))',
        'complainAutoDuplicate' => 'SUM(IF(st.type = :complainAutoDuplicate, 1, 0))',
      ])
      ->addParams([
        ':complainText' => Complain::TYPE_TEXT,
        ':complainCall' => Complain::TYPE_CALL,
        ':complainCallMno' => Complain::TYPE_CALL_MNO,
        ':complainAuto24' => Complain::TYPE_AUTO_24,
        ':complainAutoMoment' => Complain::TYPE_AUTO_MOMENT,
        ':complainAutoDuplicate' => Complain::TYPE_AUTO_DUPLICATE,
      ])
      ->from(['st' => 'complains']);
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByDates()
  {
    $this->addSelect([Group::BY_DATES => 'st.date']);
    $this->addGroupBy('st.date');
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByStreams()
  {
    $this->addSelect([Group::BY_STREAMS => 'st.stream_id']);
    $this->addGroupBy('st.stream_id');
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByMonthNumbers()
  {
    $this->addSelect([Group::BY_MONTH_NUMBERS => 'CONCAT(YEAR(st.`date`), ".", LPAD(MONTH(st.`date`), 2, "0"))']);
    $this->addGroupBy(Group::BY_MONTH_NUMBERS);
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByWeekNumbers()
  {
    $this->addSelect([Group::BY_WEEK_NUMBERS => 'CONCAT(YEAR(st.`date`), ".", LPAD(WEEK(st.`date`, 1) + 1, 2, "0"))']);
    $this->addGroupBy(Group::BY_WEEK_NUMBERS);
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByLandings()
  {
    $this->addSelect([Group::BY_LANDINGS => 'st.landing_id']);
    $this->addGroupBy('st.landing_id');
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByWebmasterSources()
  {
    $this->addSelect([Group::BY_WEBMASTER_SOURCES => 'st.source_id']);
    $this->innerJoin('sources source', 'source.id = st.source_id');
    $this->andWhere(['source.source_type' => Source::SOURCE_TYPE_WEBMASTER_SITE]);
    $this->addGroupBy('st.source_id');
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByArbitraryLinks()
  {
    $this->addSelect([Group::BY_LINKS => 'st.source_id']);
    $this->innerJoin('sources source', 'source.id = st.source_id');
    $this->andWhere(['source.source_type' => [Source::SOURCE_TYPE_LINK, Source::SOURCE_TYPE_SMART_LINK]]);
    $this->addGroupBy('st.source_id');
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByPlatforms()
  {
    $this->addSelect([Group::BY_PLATFORMS => 'st.platform_id']);
    $this->addGroupBy('st.platform_id');
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByOperators()
  {
    $this->addSelect([Group::BY_OPERATORS => 'st.operator_id']);
    $this->addGroupBy('st.operator_id');
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByCountries()
  {
    $this->addSelect([Group::BY_COUNTRIES => 'st.country_id']);
    $this->addGroupBy('st.country_id');
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByProviders()
  {
    $this->addSelect([Group::BY_PROVIDERS => 'st.provider_id']);
    $this->addGroupBy('st.provider_id');
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByUsers()
  {
    $this->addSelect([Group::BY_USERS => 'st.user_id']);
    $this->addGroupBy('st.user_id');
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByLandingPayTypes()
  {
    $this->addSelect([Group::BY_LANDING_PAY_TYPES => 'st.landing_pay_type_id']);
    $this->addGroupBy('st.landing_pay_type_id');
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByManagers()
  {
    $this->addSelect([Group::BY_MANAGERS => 'manager.id']);
    $this->addGroupBy('manager.id');
    $this->leftJoin('partners_managers pm', 'st.user_id = pm.user_id AND st.date = pm.date');
    $this->leftJoin('users manager', 'manager.id = pm.manager_id');
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByHours()
  {
    $this->addSelect([Group::BY_HOURS => 'st.hour']);
    $this->addGroupBy('st.hour');
  }
  /**
   * @inheritdoc
   */
  public function handleFilterByDates($dateFrom, $dateTo)
  {
    $this->andFilterWhere(['>=', 'st.date', $dateFrom]);
    $this->andFilterWhere(['<=', 'st.date', $dateTo]);
  }
  /**
   * @inheritdoc
   */
  public function handleFilterByLandingPayTypes($types)
  {
    $this->andFilterWhere(['st.landing_pay_type_id' => $types]);
  }
  /**
   * @inheritdoc
   */
  public function handleFilterByProviders($providers)
  {
    $this->andFilterWhere(['st.provider_id' => $providers]);
  }
  /**
   * @inheritdoc
   */
  public function handleFilterByUsers($users)
  {
    $this->andFilterWhere(['st.user_id' => $users]);
  }
  /**
   * @inheritdoc
   */
  public function handleFilterByStreams($streams)
  {
    $this->andFilterWhere(['st.stream_id' => $streams]);
  }
  /**
   * @inheritdoc
   */
  public function handleFilterBySources($sources)
  {
    $this->andFilterWhere(['st.source_id' => $sources]);
  }
  /**
   * @inheritdoc
   */
  public function handleFilterByLandings($landings)
  {
    $this->andFilterWhere(['st.landing_id' => $landings]);
  }
  /**
   * @inheritdoc
   */
  public function handleFilterByLandingCategories($landingCategories)
  {
    $this->leftJoin('landings filter_category', 'filter_category.id = st.landing_id');
    $this->andFilterWhere(['filter_category.category_id' => $landingCategories]);
  }
  /**
   * @inheritdoc
   */
  public function handleFilterByPlatforms($platforms)
  {
    $this->andFilterWhere(['st.platform_id' => $platforms]);
  }
  /**
   * @inheritdoc
   */
  public function handleFilterByFake($isFake)
  {
    if ($isFake && count($isFake) === 1 && (int)reset($isFake) === 1) {
      $this->where('0=1');
    }
  }
  /**
   * @inheritdoc
   */
  public function handleFilterByCurrency($currency)
  {
    if (!$currency) {
      return;
    }
    $this->leftJoin(
      'landing_operators',
      'landing_operators.landing_id = st.landing_id and landing_operators.operator_id = st.operator_id'
    )->andWhere([
      'or',
      ['st.landing_id' => 0],
      ['default_currency_id' => Currency::getId($currency)],
      ['is', 'landing_operators.landing_id', null]
    ]);
  }
  /**
   * @inheritdoc
   */
  public function handleFilterByCountries($countries)
  {
    $this->andFilterWhere(['st.country_id' => $countries]);
  }
  /**
   * @inheritdoc
   */
  public function handleFilterByOperators($operators)
  {
    $this->andFilterWhere(['st.operator_id' => $operators]);
  }

  /**
   * @inheritdoc
   */
  public function handleInitial(FormModel $formModel)
  {
    // Скрытие статистики недоступных пользователей для менеджеров
    UserModel::filterUsersItemsByUser($formModel->viewerId, $this, 'st', 'user_id');
  }

  /**
   * обработчик фильтрации по ревшар/цпа
   * @param string $revshareOrCpa
   */
  public function handleFilterByRevshareOrCpa($revshareOrCpa)
  {
    if (empty($revshareOrCpa)) {
      return;
    }

    $this->leftJoin('hits', 'st.hit_id = hits.id');

    if ($revshareOrCpa === FormModel::SELECT_CPA) {
      $this->andWhere(['hits.is_cpa' => 1]);
    }

    if ($revshareOrCpa === FormModel::SELECT_REVSHARE) {
      $this->andWhere(['hits.is_cpa' => 0]);
    }
  }
}
