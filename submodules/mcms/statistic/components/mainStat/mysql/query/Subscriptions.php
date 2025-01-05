<?php

namespace mcms\statistic\components\mainStat\mysql\query;

use mcms\common\helpers\Currency;
use mcms\promo\models\Source;
use mcms\statistic\components\mainStat\FormModel;
use mcms\statistic\components\mainStat\Group;
use mcms\user\models\User as UserModel;

/**
 * Достаем инфу по подпискам из таблицы statistic
 */
class Subscriptions extends BaseQuery
{

  /**
   * @inheritdoc
   */
  public function init()
  {
    parent::init();

    $this->select([
        'ons' => 'SUM(count_ons_revshare)',
        'rejected_offs' => 'SUM(count_offs_rejected)',
        'rejected_ons' => 'SUM(count_ons_rejected)',
        'cpa_ons' => 'SUM(count_ons_cpa)',
        'rejected_rebills' => 'SUM(count_rebills_rejected)',
        'sold_rebills' => 'SUM(count_rebills_sold)',
        'sold_offs' => 'SUM(count_offs_sold)',
        'offs' => 'SUM(count_offs_revshare)',
        'rebills' => 'SUM(count_rebills_revshare)',
        'partner_revshare_profit_rub' => 'SUM(partner_revshare_profit_rub)',
        'partner_revshare_profit_usd' => 'SUM(partner_revshare_profit_usd)',
        'partner_revshare_profit_eur' => 'SUM(partner_revshare_profit_eur)',
        'sold_rebills_profit_rub' => 'SUM(res_sold_profit_rub)',
        'sold_rebills_profit_usd' => 'SUM(res_sold_profit_usd)',
        'sold_rebills_profit_eur' => 'SUM(res_sold_profit_eur)',
        'rejected_profit_rub' => 'SUM(res_rejected_profit_rub)',
        'rejected_profit_usd' => 'SUM(res_rejected_profit_usd)',
        'rejected_profit_eur' => 'SUM(res_rejected_profit_eur)',
        'revshare_reseller_profit_rub' => 'SUM(res_revshare_profit_rub)',
        'revshare_reseller_profit_usd' => 'SUM(res_revshare_profit_usd)',
        'revshare_reseller_profit_eur' => 'SUM(res_revshare_profit_eur)',

        'scope_offs' => 'SUM(count_offs24_revshare)',
        'rejected_scope_offs' => 'SUM(count_offs24_rejected)',
        'sold_scope_offs' => 'SUM(count_offs24_sold)',

        'rebills_date_by_date' => 'SUM(count_rebills24_revshare)',
        'rejected_rebills_date_by_date' => 'SUM(count_rebills24_rejected)',
        'sold_rebills_date_by_date' => 'SUM(count_rebills24_sold)',

        'profit_rub_date_by_date' => 'SUM(partner_revshare_profit24_rub)',
        'profit_usd_date_by_date' => 'SUM(partner_revshare_profit24_usd)',
        'profit_eur_date_by_date' => 'SUM(partner_revshare_profit24_eur)',

        'rejected_profit_date_by_date_rub' => 'SUM(partner_rejected_profit24_rub)',
        'rejected_profit_date_by_date_usd' => 'SUM(partner_rejected_profit24_usd)',
        'rejected_profit_date_by_date_eur' => 'SUM(partner_rejected_profit24_eur)',

        'sold_profit_date_by_date_rub' => 'SUM(partner_sold_profit24_rub)',
        'sold_profit_date_by_date_usd' => 'SUM(partner_sold_profit24_usd)',
        'sold_profit_date_by_date_eur' => 'SUM(partner_sold_profit24_eur)',
      ])
      ->from(['st' => 'statistic']);
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
    $this->andFilterWhere(['st.is_fake' => $isFake]);
  }
  /**
   * @inheritdoc
   */
  public function handleFilterByCurrency($currency)
  {
    if (!$currency) {
      return;
    }
    $this->andWhere(['currency_id' => Currency::getId($currency)]);
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
    if ($revshareOrCpa === FormModel::SELECT_CPA) {
      $this->where('1=0'); // в statistic хранятся только ревшар пдп или отклоненные
    }
  }
}
