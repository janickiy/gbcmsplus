<?php

namespace mcms\statistic\components\newStat\subid\query;

use mcms\statistic\components\newStat\Group;
use Yii;

/**
 * Достаем стату по ltv
 */
class Ltv extends BaseQuery
{

  /**
   * @inheritdoc
   */
  public function init()
  {
    parent::init();

    $this->select($this->getFieldList())
      ->from(['st' => 'search_subscriptions'])
      ->leftJoin('subscription_rebills sr', 'sr.hit_id = st.hit_id')
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
      'revshare_ltv_rebills' => 'COUNT(CASE WHEN st.is_cpa = 0 THEN sr.id END)',
      'to_buyout_ltv_rebills' => 'COUNT(CASE WHEN st.is_cpa = 1 THEN sr.id END)',
      'revshare_reseller_ltv_profit_rub' => 'SUM(IF(st.is_cpa = 0, sr.reseller_profit_rub, 0))',
      'revshare_reseller_ltv_profit_usd' => 'SUM(IF(st.is_cpa = 0, sr.reseller_profit_usd, 0))',
      'revshare_reseller_ltv_profit_eur' => 'SUM(IF(st.is_cpa = 0, sr.reseller_profit_eur, 0))',

      'to_buyout_reseller_ltv_profit_rub' => 'SUM(IF(st.is_cpa = 1, sr.reseller_profit_rub, 0))',
      'to_buyout_reseller_ltv_profit_usd' => 'SUM(IF(st.is_cpa = 1, sr.reseller_profit_usd, 0))',
      'to_buyout_reseller_ltv_profit_eur' => 'SUM(IF(st.is_cpa = 1, sr.reseller_profit_eur, 0))',
      ];
  }

  /**
   * @inheritdoc
   */
  public function handleFilterByDates($dateFrom, $dateTo, $ltvDateTo = null)
  {
    if ($dateFrom) {
      $this->andWhere('st.time_on >= UNIX_TIMESTAMP(:dateFrom)', ['dateFrom' => $dateFrom]);
    }
    if ($dateTo) {
      $this->andWhere('st.time_on <= UNIX_TIMESTAMP(:dateTo) + 86399', ['dateTo' => $dateTo]);
    }

    /** @var \mcms\promo\Module $promoModule */
    $promoModule = Yii::$app->getModule('promo');
    $trialOperators = $promoModule->api('trialOperators')->getResult();

    $this->andWhere([
      'or',
      [
        'and',
        ['IN', 'st.operator_id', $trialOperators],
        ['<=', 'sr.date', Yii::$app->formatter->asDate(
          $ltvDateTo ?: $dateTo . ($dateFrom === $dateTo ? ' +1 day' : ''),
          'php:Y-m-d'
        )]
      ],
      [
        'and',
        ['NOT IN', 'st.operator_id', $trialOperators],
        ['<=', 'sr.date', $ltvDateTo ?: $dateTo]
      ]
    ]);
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
