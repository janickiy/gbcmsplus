<?php

namespace mcms\statistic\components\newStat\subid\query;

use mcms\statistic\components\newStat\Group;

/**
 * Достаем стату по подпискам без отписок
 */
class CB extends BaseQuery
{
  /**
   * @inheritdoc
   */
  public function init()
  {
    parent::init();

    $this->select($this->getFieldList());
    $this->from(['st' => "{$this->getSubidSchemaName()}.statistic_user_{$this->userId}"])
    ->leftJoin('subid_glossary sg1', 'sg1.id = st.subid1_id')
    ->leftJoin('subid_glossary sg2', 'sg2.id = st.subid2_id');
  }

  /**
   * @inheritdoc
   */
  protected function getFieldList()
  {
    return [
      'revshare_total_ons_without_offs' => 'SUM(IFNULL(revshare_ons, 0)) - SUM(IFNULL(revshare_offs, 0))',
      'to_buyout_total_ons_without_offs' => 'SUM(IFNULL(to_buyout_ons, 0)) - SUM(IFNULL(rejected_offs, 0)) - SUM(IFNULL(buyout_offs, 0))',
      ];
  }

  /**
   * @inheritdoc
   */
  public function handleFilterByDates($dateFrom, $dateTo, $ltvDateTo = null)
  {
    // Для подсчета подписок без отписок используется весь диапазон
  }

  /**
   * @inheritdoc
   */
  public function handleFilterByHour($hour)
  {
    // Для подсчета подписок без отписок используется весь диапазон
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
