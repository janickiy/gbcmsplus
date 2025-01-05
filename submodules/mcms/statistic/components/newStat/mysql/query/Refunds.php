<?php

namespace mcms\statistic\components\newStat\mysql\query;

use mcms\common\helpers\Currency;
use mcms\promo\models\Source;
use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\Group;
use mcms\statistic\models\mysql\Refund;
use mcms\user\models\User as UserModel;

/**
 * Достаем инфу по возвратам из таблицы refunds
 */
class Refunds extends BaseQuery
{

  private $isJoinedSources = false;
  private $isJoinedOperators = false;
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

    $this->from(['st' => 'refunds']);

    // иначе не определим is_cpa=1 это выкуп или OTP
    $this->leftJoin(['otp' => 'onetime_subscriptions'], 'otp.hit_id = st.hit_id');

    $this->params([
      ':rgk' => Refund::TYPE_RGK,
      ':mno' => Refund::TYPE_MNO
    ]);
  }

  /**
   * @inheritdoc
   */
  protected function getFieldList()
  {
    return [
      'revshare_rgk_refunds' => 'COUNT(IF(st.is_cpa=0 AND type=:rgk, st.id, null))',
      'to_buyout_rgk_refunds' => 'COUNT(IF(st.is_cpa=1 AND type=:rgk AND otp.id IS NULL, st.id, null))',
      'otp_rgk_refunds' => 'COUNT(IF(st.is_cpa=1 AND type=:rgk AND otp.id IS NOT NULL, st.id, null))',
      'revshare_mno_refunds' => 'COUNT(IF(st.is_cpa=0 AND type=:mno, st.id, null))',
      'to_buyout_mno_refunds' => 'COUNT(IF(st.is_cpa=1 AND type=:mno AND otp.id IS NULL, st.id, null))',
      'otp_mno_refunds' => 'COUNT(IF(st.is_cpa=1 AND type=:mno AND otp.id IS NOT NULL, st.id, null))',

      'revshare_rgk_refund_sum_rub' => 'SUM(IF(st.is_cpa=0 AND type=:rgk, st.reseller_rub, null))',
      'revshare_rgk_refund_sum_usd' => 'SUM(IF(st.is_cpa=0 AND type=:rgk, st.reseller_usd, null))',
      'revshare_rgk_refund_sum_eur' => 'SUM(IF(st.is_cpa=0 AND type=:rgk, st.reseller_eur, null))',
      'to_buyout_rgk_refund_sum_rub' => 'SUM(IF(st.is_cpa=1 AND type=:rgk AND otp.id IS NULL, st.reseller_rub, null))',
      'to_buyout_rgk_refund_sum_usd' => 'SUM(IF(st.is_cpa=1 AND type=:rgk AND otp.id IS NULL, st.reseller_usd, null))',
      'to_buyout_rgk_refund_sum_eur' => 'SUM(IF(st.is_cpa=1 AND type=:rgk AND otp.id IS NULL, st.reseller_eur, null))',
      'otp_rgk_refund_sum_rub' => 'SUM(IF(st.is_cpa=1 AND type=:rgk AND otp.id IS NOT NULL, st.reseller_rub, null))',
      'otp_rgk_refund_sum_usd' => 'SUM(IF(st.is_cpa=1 AND type=:rgk AND otp.id IS NOT NULL, st.reseller_usd, null))',
      'otp_rgk_refund_sum_eur' => 'SUM(IF(st.is_cpa=1 AND type=:rgk AND otp.id IS NOT NULL, st.reseller_eur, null))',

      'revshare_mno_refund_sum_rub' => 'SUM(IF(st.is_cpa=0 AND type=:mno, st.reseller_rub, null))',
      'revshare_mno_refund_sum_usd' => 'SUM(IF(st.is_cpa=0 AND type=:mno, st.reseller_usd, null))',
      'revshare_mno_refund_sum_eur' => 'SUM(IF(st.is_cpa=0 AND type=:mno, st.reseller_eur, null))',
      'to_buyout_mno_refund_sum_rub' => 'SUM(IF(st.is_cpa=1 AND type=:mno AND otp.id IS NULL, st.reseller_rub, null))',
      'to_buyout_mno_refund_sum_usd' => 'SUM(IF(st.is_cpa=1 AND type=:mno AND otp.id IS NULL, st.reseller_usd, null))',
      'to_buyout_mno_refund_sum_eur' => 'SUM(IF(st.is_cpa=1 AND type=:mno AND otp.id IS NULL, st.reseller_eur, null))',
      'otp_mno_refund_sum_rub' => 'SUM(IF(st.is_cpa=1 AND type=:mno AND otp.id IS NOT NULL, st.reseller_rub, null))',
      'otp_mno_refund_sum_usd' => 'SUM(IF(st.is_cpa=1 AND type=:mno AND otp.id IS NOT NULL, st.reseller_usd, null))',
      'otp_mno_refund_sum_eur' => 'SUM(IF(st.is_cpa=1 AND type=:mno AND otp.id IS NOT NULL, st.reseller_eur, null))',
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
    if ($this->isJoinedSources === false) {
      $this->isJoinedSources = true;
      $this->innerJoin('sources source', 'source.id = st.source_id');
    }
    $this->addSelect([Group::BY_STREAMS => 'source.stream_id']);
    $this->addGroupBy('source.stream_id');
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
    if ($this->isJoinedSources === false) {
      $this->isJoinedSources = true;
      $this->innerJoin('sources source', 'source.id = st.source_id');
    }
    $this->addSelect([Group::BY_WEBMASTER_SOURCES => 'st.source_id']);
    $this->andWhere(['source.source_type' => Source::SOURCE_TYPE_WEBMASTER_SITE]);
    $this->addGroupBy('st.source_id');
  }

  /**
   * @inheritdoc
   */
  public function handleGroupByArbitraryLinks()
  {
    if ($this->isJoinedSources === false) {
      $this->isJoinedSources = true;
      $this->innerJoin('sources source', 'source.id = st.source_id');
    }
    $this->addSelect([Group::BY_LINKS => 'st.source_id']);
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
    if ($this->isJoinedOperators === false) {
      $this->isJoinedOperators = true;
      $this->innerJoin('operators operator', 'operator.id = st.operator_id');
    }
    $this->addSelect([Group::BY_COUNTRIES => 'operator.country_id']);
    $this->addGroupBy('operator.country_id');
  }

  /**
   * @inheritdoc
   */
  public function handleGroupByProviders()
  {
    if ($this->isJoinedLandings === false) {
      $this->isJoinedLandings = true;
      $this->leftJoin('landings land', 'land.id = st.landing_id');
    }

    $this->addSelect([Group::BY_PROVIDERS => 'land.provider_id']);
    $this->addGroupBy(Group::BY_PROVIDERS);
  }

  /**
   * @inheritdoc
   */
  public function handleGroupByUsers()
  {
    if ($this->isJoinedSources === false) {
      $this->isJoinedSources = true;
      $this->innerJoin('sources source', 'source.id = st.source_id');
    }
    $this->addSelect([Group::BY_USERS => 'source.user_id']);
    $this->addGroupBy('source.user_id');
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
    if ($this->isJoinedSources === false) {
      $this->isJoinedSources = true;
      $this->innerJoin('sources source', 'source.id = st.source_id');
    }
    $this->addSelect([Group::BY_MANAGERS => 'manager.id']);
    $this->addGroupBy('manager.id');
    if (!$this->isJoinedManagers) {
      $this->isJoinedManagers = true;
      $this->leftJoin('partners_managers pm', 'source.user_id = pm.user_id AND st.date = pm.date');
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
    if (!$providers) {
      return;
    }
    if ($this->isJoinedLandings === false) {
      $this->isJoinedLandings = true;
      $this->leftJoin('landings land', 'land.id = st.landing_id');
    }

    $this->andFilterWhere(['land.provider_id' => $providers]);
  }

  /**
   * @inheritdoc
   */
  public function handleFilterByUsers($users)
  {
    if (!$users) {
      return;
    }

    if ($this->isJoinedSources === false) {
      $this->isJoinedSources = true;
      $this->innerJoin('sources source', 'source.id = st.source_id');
    }
    $this->andFilterWhere(['source.user_id' => $users]);
  }

  /**
   * @inheritdoc
   */
  public function handleFilterByStreams($streams)
  {
    if (!$streams) {
      return;
    }
    if ($this->isJoinedSources === false) {
      $this->isJoinedSources = true;
      $this->innerJoin('sources source', 'source.id = st.source_id');
    }
    $this->andFilterWhere(['source.stream_id' => $streams]);
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
    if (!$countries) {
      return;
    }

    if ($this->isJoinedOperators === false) {
      $this->isJoinedOperators = true;
      $this->innerJoin('operators operator', 'operator.id = st.operator_id');
    }
    $this->andFilterWhere(['operator.country_id' => $countries]);
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
    if ($this->isJoinedSources === false) {
      $this->isJoinedSources = true;
      $this->innerJoin('sources source', 'source.id = st.source_id');
    }
    // Скрытие статистики недоступных пользователей для менеджеров
    UserModel::filterUsersItemsByUser($formModel->viewerId, $this, 'source', 'user_id');
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
