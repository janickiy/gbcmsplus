<?php

namespace mcms\statistic\components\newStat;
use yii\helpers\ArrayHelper;

/**
 * Справочник соответствия колонок в статистике полям, которые достаются из БД
 * Применяется для того, чтобы понять, нужно ли использовать Query или данные из него не используются
 */
class ColumnsGlossary
{
  private static $_templateFields;

  /**
   * Карта соответствия
   * Ключ массива - сокращенный геттер из \mcms\statistic\components\newStat\mysql\Row
   * Значение массива - массив из полей \mcms\statistic\components\newStat\mysql\RowDataDto необходимых для рассчета
   * @return array
   */
  private static function getMap()
  {
    return [
      'hits' => [
        'hits',
        'tb',
        'revshare_hits',
        'cpa_hits',
        'otp_hits',
      ],
      'accepted' => [
        'hits',
        'tb',
      ],
      'unique' => [
        'unique',
        'hits',
        'tb',
      ],
      'totalCharges' => [
        'revshare_rebills',
        'buyout_rebills',
        'otp_ons',
        'revshare_offs',
        'buyout_offs',
        'revshare_total_ons_without_offs',
        'to_buyout_total_ons_without_offs'
      ],
      'aliveOns' => [
        'revshare_alive_ons',
        'to_buyout_alive_ons',
      ],
      'totalPartnerProfit' => [
        'buyout_partner_profit_rub',
        'buyout_partner_profit_usd',
        'buyout_partner_profit_eur',
        'otp_partner_profit_rub',
        'otp_partner_profit_usd',
        'otp_partner_profit_eur',
        'revshare_partner_profit_rub',
        'revshare_partner_profit_usd',
        'revshare_partner_profit_eur',
        'revshare_partner_profit_corrected_rub',
        'revshare_partner_profit_corrected_usd',
        'revshare_partner_profit_corrected_eur',
        'revshare_reseller_profit_rub',
        'revshare_reseller_profit_usd',
        'revshare_reseller_profit_eur',
        'revshare_reseller_profit_corrected_rub',
        'revshare_reseller_profit_corrected_usd',
        'revshare_reseller_profit_corrected_eur',
        'otp_reseller_profit_rub',
        'otp_reseller_profit_usd',
        'otp_reseller_profit_eur',
        'buyout_reseller_profit_rub',
        'buyout_reseller_profit_usd',
        'buyout_reseller_profit_eur',
      ],
      'totalSubscriptions' => [
        'revshare_ons',
        'buyout_ons',
        'otp_ons',
        'hits',
        'tb',
      ],
      'totalOffs' => [
        'revshare_offs',
        'rejected_offs',
        'buyout_offs',
        'revshare_total_ons_without_offs',
        'revshare_alive_ons',
        'to_buyout_total_ons_without_offs',
        'to_buyout_alive_ons',
      ],
      'revshareOffs' => [
        'revshare_offs',
        'revshare_total_ons_without_offs',
        'revshare_alive_ons',
      ],
      'toBuyoutOffs' => [
        'rejected_offs',
        'buyout_offs',
        'to_buyout_total_ons_without_offs',
        'to_buyout_alive_ons',
      ],
      'rgkComplaints' => [
        'revshare_text_complaints',
        'cpa_text_complaints',
        'otp_text_complaints',
        'revshare_call_complaints',
        'cpa_call_complaints',
        'otp_call_complaints',
        'revshare_ons',
        'buyout_ons',
        'otp_ons',
      ],
      'callMnoComplaints' => [
        'revshare_call_mno_complaints',
        'cpa_call_mno_complaints',
        'otp_call_mno_complaints',
        'revshare_ons',
        'buyout_ons',
        'otp_ons',
      ],
      'revshareHits' => [
        'revshare_hits',
        'revshare_tb',
      ],
      'revshareAccepted' => [
        'revshare_hits',
        'revshare_tb',
      ],
      'revshareUnique' => [
        'revshare_unique',
        'revshare_hits',
      ],
      'revshareOns' => [
        'revshare_ons',
        'revshare_hits',
        'revshare_tb',
      ],
      'revshareCr' => [
        'revshare_ons',
        'revshare_hits',
        'revshare_tb',
      ],
      'revshareRebills' => [
        'revshare_rebills',
        'revshare_rebills_corrected',
        'revshare_ons',
        'revshare_total_ons_without_offs',
        'revshare_alive_ons',
      ],
      'revshareRebills24' => [
        'revshare_rebills24',
        'revshare_rebills24_corrected',
        'revshare_ons',
      ],
      'revshareOffs24' => [
        'revshare_offs24',
        'revshare_ons',
      ],
      'revshareAliveOns' => [
        'revshare_alive_ons',
      ],
      'revshareRebillsTotal' => [
        'revshare_rebills',
        'revshare_rebills_corrected',
        'revshare_ons',
      ],
      'revshareResellerProfit' => [
        'revshare_reseller_profit_rub',
        'revshare_reseller_profit_usd',
        'revshare_reseller_profit_eur',
        'revshare_reseller_profit_corrected_rub',
        'revshare_reseller_profit_corrected_usd',
        'revshare_reseller_profit_corrected_eur',
      ],
      'revsharePartnerProfit' => [
        'revshare_partner_profit_rub',
        'revshare_partner_profit_usd',
        'revshare_partner_profit_eur',
        'revshare_partner_profit_corrected_rub',
        'revshare_partner_profit_corrected_usd',
        'revshare_partner_profit_corrected_eur',

        'revshare_reseller_profit_rub',
        'revshare_reseller_profit_usd',
        'revshare_reseller_profit_eur',
        'revshare_reseller_profit_corrected_rub',
        'revshare_reseller_profit_corrected_usd',
        'revshare_reseller_profit_corrected_eur',
      ],
      'totalRevsharePartnerProfit' => [
        'revshare_partner_profit_rub',
        'revshare_partner_profit_usd',
        'revshare_partner_profit_eur',
        'revshare_partner_profit_corrected_rub',
        'revshare_partner_profit_corrected_usd',
        'revshare_partner_profit_corrected_eur',

        'revshare_reseller_profit_rub',
        'revshare_reseller_profit_usd',
        'revshare_reseller_profit_eur',
        'revshare_reseller_profit_corrected_rub',
        'revshare_reseller_profit_corrected_usd',
        'revshare_reseller_profit_corrected_eur',
        'revshare_rebills',
        'revshare_rebills_corrected',
      ],
      'revshareResellerNetProfit' => [
        'revshare_reseller_profit_rub',
        'revshare_reseller_profit_usd',
        'revshare_reseller_profit_eur',
        'revshare_reseller_profit_corrected_rub',
        'revshare_reseller_profit_corrected_usd',
        'revshare_reseller_profit_corrected_eur',
        'revshare_partner_profit_rub',
        'revshare_partner_profit_usd',
        'revshare_partner_profit_eur',
        'revshare_partner_profit_corrected_rub',
        'revshare_partner_profit_corrected_usd',
        'revshare_partner_profit_corrected_eur',
      ],
      'totalRevshareResellerNetProfit' => [
        'revshare_reseller_profit_rub',
        'revshare_reseller_profit_usd',
        'revshare_reseller_profit_eur',
        'revshare_reseller_profit_corrected_rub',
        'revshare_reseller_profit_corrected_usd',
        'revshare_reseller_profit_corrected_eur',
        'revshare_partner_profit_rub',
        'revshare_partner_profit_usd',
        'revshare_partner_profit_eur',
        'revshare_partner_profit_corrected_rub',
        'revshare_partner_profit_corrected_usd',
        'revshare_partner_profit_corrected_eur',
        'otp_reseller_profit_rub',
        'otp_reseller_profit_usd',
        'otp_reseller_profit_eur',
        'buyout_reseller_profit_rub',
        'buyout_reseller_profit_usd',
        'buyout_reseller_profit_eur',
      ],
      'revshareRgkComplaints' => [
        'revshare_text_complaints',
        'revshare_call_complaints',
        'revshare_ons',
      ],
      'revshareCallMnoComplaints' => [
        'revshare_call_mno_complaints',
        'revshare_ons',
      ],
      'toBuyoutHits' => [
        'cpa_hits',
        'cpa_tb',
        'otp_hits',
        'otp_tb',
      ],
      'toBuyoutAccepted' => [
        'cpa_hits',
        'cpa_tb',
        'otp_hits',
        'otp_tb',
      ],
      'toBuyoutUnique' => [
        'cpa_unique',
        'otp_unique',
        'cpa_hits',
        'otp_hits',
      ],
      'toBuyoutOns' => [
        'to_buyout_ons',
        'buyout_ons',
        'cpa_hits',
        'cpa_tb',
        'otp_hits',
        'otp_tb',
      ],
      'buyoutCr' => [
        'buyout_ons',
        'cpa_hits',
        'cpa_tb',
        'otp_hits',
        'otp_tb',
      ],
      'buyoutVisibleOns' => [
        'buyout_visible_ons',
        'cpa_hits',
        'cpa_tb',
        'otp_hits',
        'otp_tb',
      ],
      'buyoutVisibleCr' => [
        'buyout_visible_ons',
        'cpa_hits',
        'cpa_tb',
        'otp_hits',
        'otp_tb',
      ],
      'buyoutPartnerProfit' => [
        'buyout_partner_profit_rub',
        'buyout_partner_profit_usd',
        'buyout_partner_profit_eur',
      ],
      'totalBuyoutPartnerProfit' => [
        'buyout_partner_profit_rub',
        'buyout_partner_profit_usd',
        'buyout_partner_profit_eur',
        'buyout_visible_ons',
      ],
      'buyoutAvgPartnerProfit' => [
        'buyout_ons',
        'buyout_visible_ons',
        'buyout_partner_profit_rub',
        'buyout_partner_profit_usd',
        'buyout_partner_profit_eur',
      ],
      'visibleBuyoutAvgPartnerProfit' => [
        'buyout_visible_ons',
        'buyout_partner_profit_rub',
        'buyout_partner_profit_usd',
        'buyout_partner_profit_eur',
      ],
      'buyoutRebills' => [
        'buyout_rebills',
        'to_buyout_total_ons_without_offs',
        'to_buyout_alive_ons',
      ],
      'totalRebills' => [
        'buyout_rebills',
        'revshare_rebills',
        'revshare_rebills_corrected',
      ],
      'totalRebillsRate' => [
        'buyout_rebills',
        'revshare_rebills',
        'revshare_rebills_corrected',
        'to_buyout_total_ons_without_offs',
        'to_buyout_alive_ons',
        'revshare_total_ons_without_offs',
        'revshare_alive_ons',
      ],
      'buyoutRebills24' => [
        'buyout_rebills_24',
        'buyout_ons',
      ],
      'buyoutOffs24' => [
        'buyout_offs_24',
        'buyout_ons',
      ],
      'toBuyoutAliveOns' => [
        'to_buyout_alive_ons',
      ],
      'buyoutRpm' => [
        'buyout_reseller_profit_rub',
        'buyout_reseller_profit_usd',
        'buyout_reseller_profit_eur',
        'buyout_partner_profit_rub',
        'buyout_partner_profit_usd',
        'buyout_partner_profit_eur',
        'cpa_hits',
        'cpa_tb',
        'otp_hits',
        'otp_tb',
      ],
      'buyoutResellerProfit' => [
        'buyout_reseller_profit_rub',
        'buyout_reseller_profit_usd',
        'buyout_reseller_profit_eur',
      ],
      'buyoutRoi' => [
        'to_buyout_reseller_ltv_profit_rub',
        'to_buyout_reseller_ltv_profit_usd',
        'to_buyout_reseller_ltv_profit_eur',
        'buyout_partner_profit_rub',
        'buyout_partner_profit_usd',
        'buyout_partner_profit_eur',
      ],
      'otpHits' => [
        'otp_hits',
        'otp_tb',
      ],
      'otpAccepted' => [
        'otp_hits',
        'otp_tb',
      ],
      'otpUnique' => [
        'otp_unique',
        'otp_hits',
      ],
      'otpOns' => [
        'otp_ons',
      ],
      'otpCr' => [
        'otp_ons',
        'otp_hits',
        'otp_tb',
      ],
      'otpVisibleOns' => [
        'otp_visible_ons',
        'otp_hits',
        'otp_tb',
      ],
      'otpVisibleCr' => [
        'otp_visible_ons',
        'otp_hits',
        'otp_tb',
      ],
      'otpPartnerProfit' => [
        'otp_partner_profit_rub',
        'otp_partner_profit_usd',
        'otp_partner_profit_eur',
        'otp_reseller_profit_rub',
        'otp_reseller_profit_usd',
        'otp_reseller_profit_eur',
      ],
      'totalOtpPartnerProfit' => [
        'otp_partner_profit_rub',
        'otp_partner_profit_usd',
        'otp_partner_profit_eur',
        'otp_visible_ons',
      ],
      'otpResellerProfit' => [
        'otp_reseller_profit_rub',
        'otp_reseller_profit_usd',
        'otp_reseller_profit_eur',
      ],
      'otpResellerNetProfit' => [
        'otp_partner_profit_rub',
        'otp_partner_profit_usd',
        'otp_partner_profit_eur',
        'otp_reseller_profit_rub',
        'otp_reseller_profit_usd',
        'otp_reseller_profit_eur',
      ],
      'totalOtpResellerNetProfit' => [
        'otp_partner_profit_rub',
        'otp_partner_profit_usd',
        'otp_partner_profit_eur',
        'otp_reseller_profit_rub',
        'otp_reseller_profit_usd',
        'otp_reseller_profit_eur',
        'revshare_reseller_profit_rub',
        'revshare_reseller_profit_usd',
        'revshare_reseller_profit_eur',
        'revshare_reseller_profit_corrected_rub',
        'revshare_reseller_profit_corrected_usd',
        'revshare_reseller_profit_corrected_eur',
        'buyout_reseller_profit_rub',
        'buyout_reseller_profit_usd',
        'buyout_reseller_profit_eur',
      ],
      'otpAvgPartnerProfit' => [
        'otp_ons',
        'otp_visible_ons',
        'otp_partner_profit_rub',
        'otp_partner_profit_usd',
        'otp_partner_profit_eur',
      ],
      'otpVisibleAvgPartnerProfit' => [
        'otp_visible_ons',
        'otp_partner_profit_rub',
        'otp_partner_profit_usd',
        'otp_partner_profit_eur',
      ],
      'otpRgkComplaints' => [
        'otp_text_complaints',
        'otp_call_complaints',
        'otp_ons',
      ],
      'otpCallMnoComplaints' => [
        'otp_call_mno_complaints',
        'otp_ons',
      ],
      'otpRpm' => [
        'otp_hits',
        'otp_tb',
        'otp_reseller_profit_rub',
        'otp_reseller_profit_usd',
        'otp_reseller_profit_eur',
        'otp_partner_profit_rub',
        'otp_partner_profit_usd',
        'otp_partner_profit_eur',
      ],
      'toBuyoutRgkComplaints' => [
        'buyout_text_complaints',
        'buyout_call_complaints',
        'buyout_ons',
      ],
      'toBuyoutCallMnoComplaints' => [
        'buyout_call_mno_complaints',
        'buyout_ons',
      ],
      'totalResellerNetProfit' => [
        'otp_partner_profit_rub',
        'otp_partner_profit_usd',
        'otp_partner_profit_eur',
        'otp_reseller_profit_rub',
        'otp_reseller_profit_usd',
        'otp_reseller_profit_eur',
        'revshare_reseller_profit_rub',
        'revshare_reseller_profit_usd',
        'revshare_reseller_profit_eur',
        'revshare_reseller_profit_corrected_rub',
        'revshare_reseller_profit_corrected_usd',
        'revshare_reseller_profit_corrected_eur',
        'revshare_partner_profit_rub',
        'revshare_partner_profit_usd',
        'revshare_partner_profit_eur',
        'revshare_partner_profit_corrected_rub',
        'revshare_partner_profit_corrected_usd',
        'revshare_partner_profit_corrected_eur',
        'buyout_partner_profit_rub',
        'buyout_partner_profit_usd',
        'buyout_partner_profit_eur',
        'buyout_reseller_profit_rub',
        'buyout_reseller_profit_usd',
        'buyout_reseller_profit_eur',
      ],
      'buyoutResellerNetProfit' => [
        'buyout_partner_profit_rub',
        'buyout_partner_profit_usd',
        'buyout_partner_profit_eur',
        'buyout_reseller_profit_rub',
        'buyout_reseller_profit_usd',
        'buyout_reseller_profit_eur',

        'otp_reseller_profit_rub',
        'otp_reseller_profit_usd',
        'otp_reseller_profit_eur',
        'revshare_reseller_profit_rub',
        'revshare_reseller_profit_usd',
        'revshare_reseller_profit_eur',
        'revshare_reseller_profit_corrected_rub',
        'revshare_reseller_profit_corrected_usd',
        'revshare_reseller_profit_corrected_eur',
      ],
      'totalResellerProfit' => [
        'otp_reseller_profit_rub',
        'otp_reseller_profit_usd',
        'otp_reseller_profit_eur',
        'revshare_reseller_profit_rub',
        'revshare_reseller_profit_usd',
        'revshare_reseller_profit_eur',
        'revshare_reseller_profit_corrected_rub',
        'revshare_reseller_profit_corrected_usd',
        'revshare_reseller_profit_corrected_eur',
        'buyout_reseller_profit_rub',
        'buyout_reseller_profit_usd',
        'buyout_reseller_profit_eur',
      ],
      'totalArpu' => [
        'revshare_reseller_ltv_profit_rub',
        'revshare_reseller_ltv_profit_usd',
        'revshare_reseller_ltv_profit_eur',
        'revshare_ltv_rebills',
        'revshare_ons',
        'to_buyout_reseller_ltv_profit_rub',
        'to_buyout_reseller_ltv_profit_usd',
        'to_buyout_reseller_ltv_profit_eur',
        'to_buyout_ltv_rebills',
        'to_buyout_ons',
      ],
      'revshareArpu' => [
        'revshare_reseller_ltv_profit_rub',
        'revshare_reseller_ltv_profit_usd',
        'revshare_reseller_ltv_profit_eur',
        'revshare_ltv_rebills',
        'revshare_ons',
      ],
      'toBuyoutArpu' => [
        'to_buyout_reseller_ltv_profit_rub',
        'to_buyout_reseller_ltv_profit_usd',
        'to_buyout_reseller_ltv_profit_eur',
        'to_buyout_ltv_rebills',
        'to_buyout_ons',
      ],
      'totalOnsWithoutOffs' => [
        'revshare_total_ons_without_offs',
        'revshare_alive30_ons',
        'to_buyout_total_ons_without_offs',
        'to_buyout_alive30_ons',
      ],
      'revshareTotalOnsWithoutOffs' => [
        'revshare_total_ons_without_offs',
        'revshare_alive30_ons',
      ],
      'toBuyoutTotalOnsWithoutOffs' => [
        'to_buyout_total_ons_without_offs',
        'to_buyout_alive30_ons',
      ],
    ];
  }





  /**
   * Получение имен полей из БД, которые необходимы для шаблона
   * @param int $templateId
   * @return array
   */
  public static function getTemplateFields($templateId)
  {
    $templateFields = ArrayHelper::getValue(self::$_templateFields, $templateId);
    if ($templateFields !== null) {
      return $templateFields;
    }
    $result = [];
    $templateColumns = Grid::getTemplateColumnsList($templateId);

    foreach (self::getMap() as $item => $value) {
      if (in_array($item, $templateColumns)) {
        $result = array_merge($result, $value);
      }
    }
    self::$_templateFields[$templateId] =  array_unique($result);

    return self::$_templateFields[$templateId];
  }
}