<?php

namespace mcms\statistic\components\newStat\subid\query;

use mcms\statistic\components\newStat\Group;
use yii\base\InvalidParamException;

/**
 * Достаем стату сгруппированную по subid
 */
class StatisticUser extends BaseQuery
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
      'hits' => 'SUM(IFNULL(hits, 0))',
      'tb' => 'SUM(IFNULL(tb, 0))',
      'unique' => 'SUM(IFNULL(uniques, 0))',

      'to_buyout_hits' => 'SUM(IFNULL(to_buyout_hits, 0))',
      'to_buyout_unique' => 'SUM(IFNULL(to_buyout_uniques, 0))',
      'to_buyout_tb' => 'SUM(IFNULL(to_buyout_tb, 0))',

      'revshare_hits' => 'SUM(IFNULL(revshare_hits, 0))',
      'revshare_unique' => 'SUM(IFNULL(revshare_uniques, 0))',
      'revshare_tb' => 'SUM(IFNULL(revshare_tb, 0))',

      'otp_hits' => 'SUM(IFNULL(otp_hits, 0))',
      'otp_unique' => 'SUM(IFNULL(otp_uniques, 0))',
      'otp_tb' => 'SUM(IFNULL(otp_tb, 0))',

      'revshare_ons' => 'SUM(IFNULL(revshare_ons, 0))',
      'to_buyout_ons' => 'SUM(IFNULL(to_buyout_ons, 0))',
      'otp_ons' => 'SUM(IFNULL(otp_ons, 0))',
      'buyout_ons' => 'SUM(IFNULL(buyout_ons, 0))',

      'revshare_offs' => 'SUM(IFNULL(revshare_offs, 0))',
      'buyout_offs' => 'SUM(IFNULL(buyout_offs, 0))',
      'rejected_offs' => 'SUM(IFNULL(rejected_offs, 0))',

      'revshare_rebills' => 'SUM(IFNULL(revshare_rebills, 0))',
      'revshare_rebills_corrected' => 'SUM(IFNULL(revshare_corrected_rebills, 0))',
      'buyout_rebills' => 'SUM(IFNULL(buyout_rebills, 0))',

      'buyout_partner_profit_rub' => 'SUM(IFNULL(buyout_partner_profit_rub, 0))',
      'buyout_partner_profit_usd' => 'SUM(IFNULL(buyout_partner_profit_usd, 0))',
      'buyout_partner_profit_eur' => 'SUM(IFNULL(buyout_partner_profit_eur, 0))',

      'buyout_visible_ons' => 'SUM(IFNULL(buyout_visible_ons, 0))',

      'revshare_partner_profit_rub' => 'SUM(IFNULL(revshare_partner_profit_rub, 0))',
      'revshare_partner_profit_usd' => 'SUM(IFNULL(revshare_partner_profit_usd, 0))',
      'revshare_partner_profit_eur' => 'SUM(IFNULL(revshare_partner_profit_eur, 0))',

      'otp_partner_profit_rub' => 'SUM(IFNULL(otp_partner_profit_rub, 0))',
      'otp_partner_profit_usd' => 'SUM(IFNULL(otp_partner_profit_usd, 0))',
      'otp_partner_profit_eur' => 'SUM(IFNULL(otp_partner_profit_eur, 0))',

      'otp_visible_ons' => 'SUM(IFNULL(otp_visible_ons, 0))',

      'otp_reseller_profit_rub' => 'SUM(IFNULL(otp_reseller_profit_rub, 0))',
      'otp_reseller_profit_usd' => 'SUM(IFNULL(otp_reseller_profit_usd, 0))',
      'otp_reseller_profit_eur' => 'SUM(IFNULL(otp_reseller_profit_eur, 0))',

      'buyout_reseller_profit_rub' => 'SUM(IFNULL(buyout_res_profit_rub, 0))',
      'buyout_reseller_profit_usd' => 'SUM(IFNULL(buyout_res_profit_usd, 0))',
      'buyout_reseller_profit_eur' => 'SUM(IFNULL(buyout_res_profit_eur, 0))',

      'revshare_reseller_profit_rub' => 'SUM(IFNULL(revshare_res_profit_rub, 0))',
      'revshare_reseller_profit_usd' => 'SUM(IFNULL(revshare_res_profit_usd, 0))',
      'revshare_reseller_profit_eur' => 'SUM(IFNULL(revshare_res_profit_eur, 0))',

      'to_buyout_text_complaints' => 'SUM(IFNULL(to_buyout_text_complaints, 0))',
      'to_buyout_call_complaints' => 'SUM(IFNULL(to_buyout_call_complaints, 0))',

      'revshare_text_complaints' => 'SUM(IFNULL(revshare_text_complaints, 0))',
      'revshare_call_complaints' => 'SUM(IFNULL(revshare_call_complaints, 0))',

      'otp_text_complaints' => 'SUM(IFNULL(otp_text_complaints, 0))',
      'otp_call_complaints' => 'SUM(IFNULL(otp_call_complaints, 0))',

      'to_buyout_call_mno_complaints' => 'SUM(IFNULL(to_buyout_call_mno_complaints, 0))',
      'revshare_call_mno_complaints' => 'SUM(IFNULL(revshare_call_mno_complaints, 0))',
      'otp_call_mno_complaints' => 'SUM(IFNULL(otp_call_mno_complaints, 0))',

      'revshare_rgk_refund_sum_rub' => 'SUM(IFNULL(revshare_rgk_refund_sum_rub, 0))',
      'revshare_rgk_refund_sum_usd' => 'SUM(IFNULL(revshare_rgk_refund_sum_usd, 0))',
      'revshare_rgk_refund_sum_eur' => 'SUM(IFNULL(revshare_rgk_refund_sum_eur, 0))',

      'to_buyout_rgk_refund_sum_rub' => 'SUM(IFNULL(to_buyout_rgk_refund_sum_rub, 0))',
      'to_buyout_rgk_refund_sum_usd' => 'SUM(IFNULL(to_buyout_rgk_refund_sum_usd, 0))',
      'to_buyout_rgk_refund_sum_eur' => 'SUM(IFNULL(to_buyout_rgk_refund_sum_eur, 0))',

      'otp_rgk_refund_sum_rub' => 'SUM(IFNULL(otp_rgk_refund_sum_rub, 0))',
      'otp_rgk_refund_sum_usd' => 'SUM(IFNULL(otp_rgk_refund_sum_usd, 0))',
      'otp_rgk_refund_sum_eur' => 'SUM(IFNULL(otp_rgk_refund_sum_eur, 0))',

      'revshare_rgk_refunds' => 'SUM(IFNULL(revshare_rgk_refunds, 0))',
      'to_buyout_rgk_refunds' => 'SUM(IFNULL(to_buyout_rgk_refunds, 0))',
      'otp_rgk_refunds' => 'SUM(IFNULL(otp_rgk_refunds, 0))',

      'revshare_mno_refund_sum_rub' => 'SUM(IFNULL(revshare_mno_refund_sum_rub, 0))',
      'revshare_mno_refund_sum_usd' => 'SUM(IFNULL(revshare_mno_refund_sum_usd, 0))',
      'revshare_mno_refund_sum_eur' => 'SUM(IFNULL(revshare_mno_refund_sum_eur, 0))',

      'to_buyout_mno_refund_sum_rub' => 'SUM(IFNULL(to_buyout_mno_refund_sum_rub, 0))',
      'to_buyout_mno_refund_sum_usd' => 'SUM(IFNULL(to_buyout_mno_refund_sum_usd, 0))',
      'to_buyout_mno_refund_sum_eur' => 'SUM(IFNULL(to_buyout_mno_refund_sum_eur, 0))',

      'otp_mno_refund_sum_rub' => 'SUM(IFNULL(otp_mno_refund_sum_rub, 0))',
      'otp_mno_refund_sum_usd' => 'SUM(IFNULL(otp_mno_refund_sum_usd, 0))',
      'otp_mno_refund_sum_eur' => 'SUM(IFNULL(otp_mno_refund_sum_eur, 0))',

      'revshare_mno_refunds' => 'SUM(IFNULL(revshare_mno_refunds, 0))',
      'to_buyout_mno_refunds' => 'SUM(IFNULL(to_buyout_mno_refunds, 0))',
      'otp_mno_refunds' => 'SUM(IFNULL(otp_mno_refunds, 0))',

      'buyout_rebills24' => 'SUM(IFNULL(buyout_rebills24, 0))',
      'buyout_offs24' => 'SUM(IFNULL(buyout_offs24, 0))',

      'revshare_rebills24' => 'SUM(IFNULL(revshare_rebills24, 0))',
      'revshare_rebills24_corrected' => 'SUM(IFNULL(revshare_corrected_rebills24, 0))',
      'revshare_offs24' => 'SUM(IFNULL(revshare_offs24, 0))',

      'revshare_reseller_profit_corrected_rub' => 'SUM(IFNULL(revshare_corrected_reseller_profit_rub, 0))',
      'revshare_reseller_profit_corrected_usd' => 'SUM(IFNULL(revshare_corrected_reseller_profit_usd, 0))',
      'revshare_reseller_profit_corrected_eur' => 'SUM(IFNULL(revshare_corrected_reseller_profit_eur, 0))',

      'otp_reseller_corrected_profit_rub' => 'SUM(IFNULL(otp_reseller_corrected_profit_rub, 0))',
      'otp_reseller_corrected_profit_usd' => 'SUM(IFNULL(otp_reseller_corrected_profit_usd, 0))',
      'otp_reseller_corrected_profit_eur' => 'SUM(IFNULL(otp_reseller_corrected_profit_eur, 0))',

      ];
  }





  /**
   * @inheritdoc
   */
  public function handleFilterByDates($dateFrom, $dateTo, $ltvDateTo = null)
  {
    $this->andFilterWhere(['>=', 'st.date', $dateFrom]);
    $this->andFilterWhere(['<=', 'st.date', $dateTo]);
  }

  /**
   * @inheritdoc
   */
  public function handleFilterByHour($hour)
  {
    if ($hour === null || $hour === '') {
      return;
    }
    $this->andFilterWhere(['st.hour' => $hour]);
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
