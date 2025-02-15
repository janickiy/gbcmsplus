<?php

namespace mcms\statistic\components\newStat\mysql\query;

use mcms\common\helpers\Currency;
use mcms\promo\models\Source;
use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\Group;
use mcms\user\models\User as UserModel;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * Достаем инфу по живым подпискам
 */
class AliveSearch extends BaseQuery
{

  /**
   * @var Query
   */
  public $innerHourQuery;

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

    $this->from(['st' => 'alive_ons_day_group'])
      ->leftJoin('days d', '1=1');

    $this->innerHourQuery = (new Query())
      ->select(['hour'])
      ->from('hours')
      ->groupBy('hour');
  }
  /**
   * @inheritdoc
   */
  protected function getFieldList()
  {
    return [
      'revshare_alive_ons' => 'SUM(total_ons_without_offs_revshare)',
      'to_buyout_alive_ons' => 'SUM(total_ons_without_offs_cpa)',
    ];
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByDates()
  {
    $this->addSelect([Group::BY_DATES => 'd.date']);
    $this->addGroupBy('d.date');
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
    $this->addSelect([Group::BY_MONTH_NUMBERS => 'CONCAT(YEAR(d.`date`), ".", LPAD(MONTH(d.`date`), 2, "0"))']);
    $this->addGroupBy(Group::BY_MONTH_NUMBERS);
  }
  /**
   * @inheritdoc
   */
  public function handleGroupByWeekNumbers()
  {
    $this->addSelect([Group::BY_WEEK_NUMBERS => 'CONCAT(YEAR(d.`date`), ".", LPAD(WEEK(d.`date`, 1) + 1, 2, "0"))']);
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
      $this->leftJoin('partners_managers pm', 'st.user_id = pm.user_id AND d.date = pm.date');
      $this->leftJoin('users manager', 'manager.id = pm.manager_id');
    }
  }

  /**
   * @inheritdoc
   */
  public function handleGroupByHours()
  {
    $this->leftJoin(['internal' => $this->innerHourQuery], '1=1');
    $this->addSelect([Group::BY_HOURS => 'internal.hour']);
    $this->addGroupBy(Group::BY_HOURS);
  }
  /**
   * @inheritdoc
   */
  public function handleFilterByDates($dateFrom, $dateTo, $ltvDateTo)
  {
    $today = Yii::$app->formatter->asDate('today', 'php:Y-m-d');

    $this->andFilterWhere(['>=', 'd.date', $dateFrom]);
    $this->andFilterWhere(['<=', 'd.date', $dateTo]);

    // Берем данные на сегодня или $ltvDateTo, если выбрана
    $this->andFilterWhere(['st.date' => $ltvDateTo ?: $today]);

    if ($today === $dateTo) {
      // А сегодня в завтрашний день не все могут смотреть. Вернее смотреть могут не только лишь все, мало кто может это делать
      // короче чтоб не отобразились часы за сегодня, которые ещё не наступили
      $this->innerHourQuery->andWhere(['<=', 'hour', new Expression("FROM_UNIXTIME(UNIX_TIMESTAMP(), '%k')")]);
    }

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
    $this->andFilterWhere(['st.is_fake' => $isFake]);
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
      $this->leftJoin('partners_managers pm', 'st.user_id = pm.user_id AND d.date = pm.date');
      $this->leftJoin('users manager', 'manager.id = pm.manager_id');
    }
  }

  /**
   * Живые пдп в разрезе по часам недоступны
   * @inheritdoc
   */
  public function handleFilterByHour($hour)
  {
    // не меняем значение при фильтрации по часам
  }

  /**
   * @inheritdoc
   */
  public function handleInitial(FormModel $formModel)
  {
    // Скрытие статистики недоступных пользователей для менеджеров
    UserModel::filterUsersItemsByUser($formModel->viewerId, $this, 'st', 'user_id');


    // для группировки по неделям мы отсекаем значения, которые не равны воскресенью. Но за исключением даты правой границы фильтрации
    if (in_array(Group::BY_WEEK_NUMBERS, $formModel->groups, true)) {
      $this->andWhere([
        'or',
        ['d.date' => $formModel->getDateTo()],
        new Expression('DAYOFWEEK(d.date) = 1')
      ]);
    }

    // для группировки по месяцам мы отсекаем значения, которые не равны последним дням месяцев. Но за исключением даты правой границы фильтрации
    if (in_array(Group::BY_MONTH_NUMBERS, $formModel->groups, true)) {
      $this->andWhere([
        'or',
        ['d.date' => $formModel->getDateTo()],
        ['d.date' => new Expression('LAST_DAY(d.date)')],
      ]);
    }
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
