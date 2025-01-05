<?php

namespace mcms\statistic\components\newStat\subid;

/**
 * Исходные данные, полученные из БД.
 */
class RowDataDto
{
  /**
   * @var int
   */
  public $hits;
  /**
   * @var int
   */
  public $tb;
  /**
   * @var int
   */
  public $unique;
  /**
   * @var int
   */
  public $toBuyoutHits;
  /**
   * @var int
   */
  public $toBuyoutTb;
  /**
   * @var int
   */
  public $toBuyoutUnique;
  /**
   * @var int
   */
  public $revshareHits;
  /**
   * @var int
   */
  public $revshareTb;
  /**
   * @var int
   */
  public $revshareUnique;
  /**
   * @var int
   */
  public $otpHits;
  /**
   * @var int
   */
  public $otpTb;
  /**
   * @var int
   */
  public $otpUnique;
  /**
   * @var int
   */
  public $revshareOns;
  /**
   * @var int
   */
  public $toBuyoutOns;
  /**
   * @var int
   */
  public $buyoutOns;
  /**
   * @var int
   */
  public $otpOns;
  /**
   * @var int
   */
  public $revshareOffs;
  /**
   * @var int
   */
  public $buyoutOffs;
  /**
   * @var int
   */
  public $rejectedOffs;
  /**
   * @var int
   */
  public $revshareTotalOnsWithoutOffs;
  /**
   * @var int
   */
  public $toBuyoutTotalOnsWithoutOffs;
  /**
   * @var int
   */
  public $revshareAlive30Ons;
  /**
   * @var int
   */
  public $toBuyoutAlive30Ons;
  /**
   * @var int
   */
  public $revshareRebills;
  /**
   * @var int
   */
  public $revshareRebillsCorrected;
  /**
   * @var int
   */
  public $buyoutRebills;
  /**
   * @var int
   */
  public $revshareLtvRebills;
  /**
   * @var int
   */
  public $toBuyoutLtvRebills;
  /**
   * @var float
   */
  public $revshareResellerLtvProfitRub;
  /**
   * @var float
   */
  public $revshareResellerLtvProfitUsd;
  /**
   * @var float
   */
  public $revshareResellerLtvProfitEur;
  /**
   * @var float
   */
  public $toBuyoutResellerLtvProfitRub;
  /**
   * @var float
   */
  public $toBuyoutResellerLtvProfitUsd;
  /**
   * @var float
   */
  public $toBuyoutResellerLtvProfitEur;
  /**
   * @var int
   */
  public $revshareAliveOns;
  /**
   * @var int
   */
  public $toBuyoutAliveOns;
  /**
   * @var float
   */
  public $buyoutPartnerProfitRub;
  /**
   * @var float
   */
  public $buyoutPartnerProfitUsd;
  /**
   * @var float
   */
  public $buyoutPartnerProfitEur;
  /**
   * @var int
   */
  public $buyoutVisibleOns;
  /**
   * @var float
   */
  public $revsharePartnerProfitRub;
  /**
   * @var float
   */
  public $revsharePartnerProfitUsd;
  /**
   * @var float
   */
  public $revsharePartnerProfitEur;
  /**
   * @var float
   */
  public $otpPartnerProfitRub;
  /**
   * @var float
   */
  public $otpPartnerProfitUsd;
  /**
   * @var float
   */
  public $otpPartnerProfitEur;
  /**
   * @var int
   */
  public $otpVisibleOns;
  /**
   * @var float
   */
  public $otpResellerProfitRub;
  /**
   * @var float
   */
  public $otpResellerProfitUsd;
  /**
   * @var float
   */
  public $otpResellerProfitEur;
  /**
   * @var float
   */
  public $buyoutResellerProfitRub;
  /**
   * @var float
   */
  public $buyoutResellerProfitUsd;
  /**
   * @var float
   */
  public $buyoutResellerProfitEur;
  /**
   * @var float
   */
  public $revshareResellerProfitRub;
  /**
   * @var float
   */
  public $revshareResellerProfitUsd;
  /**
   * @var float
   */
  public $revshareResellerProfitEur;
  /**
   * @var int
   */
  public $toBuyoutCallComplaints;
  /**
   * @var int
   */
  public $toBuyoutTextComplaints;
  /**
   * @var int
   */
  public $revshareCallComplaints;
  /**
   * @var int
   */
  public $revshareTextComplaints;
  /**
   * @var int
   */
  public $otpCallComplaints;
  /**
   * @var int
   */
  public $otpTextComplaints;
  /**
   * @var int
   */
  public $toBuyoutCallMnoComplaints;
  /**
   * @var int
   */
  public $revshareCallMnoComplaints;
  /**
   * @var int
   */
  public $otpCallMnoComplaints;
  /**
   * @var float
   */
  public $revshareRgkRefundSumRub;
  /**
   * @var float
   */
  public $revshareRgkRefundSumUsd;
  /**
   * @var float
   */
  public $revshareRgkRefundSumEur;
  /**
   * @var float
   */
  public $toBuyoutRgkRefundSumRub;
  /**
   * @var float
   */
  public $toBuyoutRgkRefundSumUsd;
  /**
   * @var float
   */
  public $toBuyoutRgkRefundSumEur;
  /**
   * @var float
   */
  public $otpRgkRefundSumRub;
  /**
   * @var float
   */
  public $otpRgkRefundSumUsd;
  /**
   * @var float
   */
  public $otpRgkRefundSumEur;
  /**
   * @var int
   */
  public $revshareRgkRefunds;
  /**
   * @var int
   */
  public $toBuyoutRgkRefunds;
  /**
   * @var int
   */
  public $otpRgkRefunds;
  /**
   * @var float
   */
  public $revshareMnoRefundSumRub;
  /**
   * @var float
   */
  public $revshareMnoRefundSumUsd;
  /**
   * @var float
   */
  public $revshareMnoRefundSumEur;
  /**
   * @var float
   */
  public $toBuyoutMnoRefundSumRub;
  /**
   * @var float
   */
  public $toBuyoutMnoRefundSumUsd;
  /**
   * @var float
   */
  public $toBuyoutMnoRefundSumEur;
  /**
   * @var float
   */
  public $otpMnoRefundSumRub;
  /**
   * @var float
   */
  public $otpMnoRefundSumUsd;
  /**
   * @var float
   */
  public $otpMnoRefundSumEur;
  /**
   * @var int
   */
  public $revshareMnoRefunds;
  /**
   * @var int
   */
  public $toBuyoutMnoRefunds;
  /**
   * @var int
   */
  public $otpMnoRefunds;
  /**
   * @var int
   */
  public $buyoutRebills24;
  /**
   * @var int
   */
  public $buyoutOffs24;
  /**
   * @var int
   */
  public $revshareRebills24;
  /**
   * @var int
   */
  public $revshareRebills24Corrected;
  /**
   * @var int
   */
  public $revshareOffs24;
  /**
   * @var float
   */
  public $revshareResellerProfitCorrectedRub;
  /**
   * @var float
   */
  public $revshareResellerProfitCorrectedUsd;
  /**
   * @var float
   */
  public $revshareResellerProfitCorrectedEur;
  /**
   * @var float
   */
  public $otpResellerCorrectedProfitRub;
  /**
   * @var float
   */
  public $otpResellerCorrectedProfitUsd;
  /**
   * @var float
   */
  public $otpResellerCorrectedProfitEur;


  /**
   * todo временное решение, пока не сделаем нормальный абстрактный Row.
   * todo Иначе мускульный Row ругается на отсутствие в этом объекте некоторых мускульных свойств
   * @param $name
   * @return int
   */
  public function __get($name)
  {
    if (property_exists($this, $name)) {
      return $this->$name;
    }

    return 0;
  }


}
