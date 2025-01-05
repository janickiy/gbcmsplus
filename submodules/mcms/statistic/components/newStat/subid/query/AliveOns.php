<?php

namespace mcms\statistic\components\newStat\subid\query;

use mcms\statistic\components\newStat\Group;
use Yii;
use yii\db\Expression;

/**
 * Достаем стату по живым подпискам в когортах
 */
class AliveOns extends BaseQuery
{
  /**
   * @const int кол-во дней, которое учитываем при подсчете живих подписок
   */
  const ALIVE_DAYS_PERIOD = 30;

  /**
   * @inheritdoc
   */
  public function init()
  {
    parent::init();

    $this->select($this->getFieldList())
      ->from(['st' => 'search_subscriptions'])
      ->leftJoin('subid_glossary sg1', 'sg1.id = st.subid1_id')
      ->leftJoin('subid_glossary sg2', 'sg2.id = st.subid2_id')
      ->andWhere(['st.user_id' => $this->userId]);
  }

  /**
   * @inheritdoc
   */
  protected function getFieldList()
  {
    return [
      'revshare_alive_ons' => 'COUNT(CASE WHEN st.is_cpa = 0 THEN st.hit_id END)',
      'to_buyout_alive_ons' => 'COUNT(CASE WHEN st.is_cpa = 1 THEN st.hit_id END)',
      ];
  }

  /**
   * @inheritdoc
   */
  public function handleFilterByDates($dateFrom, $dateTo, $ltvDateTo = null)
  {
    $this->andFilterWhere(['>=', 'st.time_on', new Expression('UNIX_TIMESTAMP(:dateFrom)', [':dateFrom' => $dateFrom])]);
    $this->andFilterWhere(['<=', 'st.time_on', new Expression('UNIX_TIMESTAMP(:dateTo) + 86399', [':dateTo' => $dateTo])]);

    if ($ltvDateTo && $ltvDateTo < $dateFrom) {
      // нет никакого логического смысла так фильтровать вообще
      $this->andWhere('1=0');
    }

    $offsDateTo = $ltvDateTo ?: $dateTo;

    if (!$offsDateTo) {
      $offsDateTo = Yii::$app->formatter->asDate('today', 'php:Y-m-d');
    }

    $this->andWhere('(st.time_off > UNIX_TIMESTAMP(:ltvDateTo) + 86399) OR st.time_off = 0', [':ltvDateTo' => $offsDateTo]);
  }

  /**
   * @inheritdoc
   */
  public function handleFilterByHour($hour)
  {
    $this->andFilterWhere(['FROM_UNIXTIME(st.time_on, "%k")' => $hour]);
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
    $this->andFilterWhere(['st.category_id' => $landingCategories]);
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
  public function handleFilterByFake($fake)
  {
    $this->andFilterWhere(['st.is_fake' => $fake]);
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
  public function handleGroupBySubid1()
  {
    $this->addGroupBy([Group::BY_SUBID_1 => 'subid1_id']);
    $this->addSelect([Group::BY_SUBID_1 => 'sg1.value']);
  }

  /**
   * @inheritdoc
   */
  public function handleGroupBySubid2()
  {
    $this->addGroupBy([Group::BY_SUBID_2 => 'subid2_id']);
    $this->addSelect([Group::BY_SUBID_2 => 'sg2.value']);
  }

  /**
   * фильтрация по subid1
   * @param string|string[] $values
   */
  public function handleFilterBySubid1($values)
  {
    $this->andFilterWhere(['sg1.value' => $values]);
  }

  /**
   * фильтрация по subid2
   * @param string|string[] $values
   */
  public function handleFilterBySubid2($values)
  {
    $this->andFilterWhere(['sg2.value' => $values]);
  }
}
