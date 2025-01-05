<?php

namespace mcms\statistic\components\newStat\mysql\query;

use mcms\common\helpers\Currency;
use mcms\promo\models\Source;
use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\Group;
use mcms\user\models\User as UserModel;

/**
 * Достаем инфу по подпискам из таблицы sold_subscriptions
 */
class Buyout extends BaseQuery
{

  /**
   * @var bool чтобы не джойнить одну таблицу дважды, после 1го джойна ставим этот флаг в true
   */
  private $isJoinedLandings = false;

  /**
   * @inheritdoc
   */
  public function init()
  {
    parent::init();

    $this->from(['st' => 'sold_subscriptions']);
  }

  /**
   * @inheritdoc
   */
  protected function getFieldList()
  {
    return [
      'buyout_ons' => 'COUNT(st.hit_id)',
      'buyout_visible_ons' => 'SUM(st.is_visible_to_partner)',
      'buyout_partner_profit_rub' => 'SUM(IF(st.is_visible_to_partner = 1, profit_rub, 0))',
      'buyout_partner_profit_usd' => 'SUM(IF(st.is_visible_to_partner = 1, profit_usd, 0))',
      'buyout_partner_profit_eur' => 'SUM(IF(st.is_visible_to_partner = 1, profit_eur, 0))',
    ];
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
    if (!$this->isJoinedManagers) {
      $this->isJoinedManagers = true;
      $this->leftJoin('partners_managers pm', 'st.user_id = pm.user_id AND st.date = pm.date');
      $this->leftJoin('users manager', 'manager.id = pm.manager_id');
    }
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
  public function handleFilterByDates($dateFrom, $dateTo, $ltvDateTo)
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
    if (!$landingCategories) {
      return;
    }
    $this->joinLandings();
    $this->andFilterWhere(['land.category_id' => $landingCategories]);
  }

  /**
   * @inheritdoc
   */
  public function handleFilterByOfferCategories($offerCategories)
  {
    if (!$offerCategories) {
      return;
    }
    $this->joinLandings();
    $this->andFilterWhere(['land.offer_category_id' => $offerCategories]);
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
  public function handleFilterByManagers($managers)
  {
    $this->andFilterWhere(['pm.manager_id' => $managers]);
    if (!$this->isJoinedManagers && $managers) {
      $this->isJoinedManagers = true;
      $this->leftJoin('partners_managers pm', 'st.user_id = pm.user_id AND st.date = pm.date');
      $this->leftJoin('users manager', 'manager.id = pm.manager_id');
    }
  }

  /**
   * @inheritdoc
   */
  public function handleFilterByHour($hour)
  {
    $this->andFilterWhere(['st.hour' => $hour]);
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
   * @inheritdoc
   */
  public function handleGroupBySources()
  {
    $this->addSelect([Group::BY_ALL_SOURCES => 'st.source_id']);
    $this->addGroupBy(Group::BY_ALL_SOURCES);
  }

  private function joinLandings()
  {
    if ($this->isJoinedLandings) {
      return;
    }
    $this->leftJoin('landings land', 'land.id = st.landing_id');
    $this->isJoinedLandings = true;
  }

  /**
   * @inheritdoc
   */
  public function handleGroupByLandingCategories()
  {
    $this->joinLandings();
    $this->addSelect([Group::BY_LANDING_CATEGORIES => 'land.category_id']);
    $this->addGroupBy(Group::BY_LANDING_CATEGORIES);
  }

  /**
   * @inheritdoc
   */
  public function handleGroupByOfferCategories()
  {
    $this->joinLandings();
    $this->addSelect([Group::BY_OFFER_CATEGORIES => 'land.offer_category_id']);
    $this->addGroupBy(Group::BY_OFFER_CATEGORIES);
  }
}
