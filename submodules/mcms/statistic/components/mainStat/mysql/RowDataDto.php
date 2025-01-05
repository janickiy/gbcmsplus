<?php

namespace mcms\statistic\components\mainStat\mysql;

/**
 * Исходные данные, полученные из БД.
 */
class RowDataDto
{
  /**
   * @var int
   * @see Row::getHits()
   */
  public $hits;
  /**
   * @var int
   * @see Row::getUniques()
   */
  public $uniques;
  /**
   * @var int
   * @see Row::getTb()
   */
  public $tb;
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
  public $cpaUniques;
  /**
   * @var int
   */
  public $revshareUniques;
  /**
   * @var int
   */
  public $onetimeUniques;
  /**
   * @var int
   */
  public $cpaUniquesTb;
  /**
   * @var int
   */
  public $revshareUniquesTb;
  /**
   * @var int
   */
  public $onetimeUniquesTb;
  /**
   * @var int
   */
  public $ons;
  /**
   * @var int
   */
  public $rejectedOffs;
  /**
   * @var int
   */
  public $rejectedOns;
  /**
   * @var int
   */
  public $cpaOns;
  /**
   * @var int
   */
  public $rejectedRebills;
  /**
   * @var int
   */
  public $soldRebills;
  /**
   * @var int
   */
  public $soldOffs;
  /**
   * @var int
   */
  public $offs;
  /**
   * @var int
   */
  public $rebills;
  /**
   * @var float
   */
  public $partnerRevshareProfitRub;
  /**
   * @var float
   */
  public $partnerRevshareProfitUsd;
  /**
   * @var float
   */
  public $partnerRevshareProfitEur;
  /**
   * @var float
   */
  public $soldRebillsProfitRub;
  /**
   * @var float
   */
  public $soldRebillsProfitUsd;
  /**
   * @var float
   */
  public $soldRebillsProfitEur;
  /**
   * @var float
   */
  public $rejectedProfitRub;
  /**
   * @var float
   */
  public $rejectedProfitUsd;
  /**
   * @var float
   */
  public $rejectedProfitEur;
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
  public $scopeOffs;
  /**
   * @var int
   */
  public $rejectedScopeOffs;
  /**
   * @var int
   */
  public $soldScopeOffs;
  /**
   * @var int
   */
  public $rebillsDateByDate;
  /**
   * @var int
   */
  public $rejectedRebillsDateByDate;
  /**
   * @var int
   */
  public $soldRebillsDateByDate;
  /**
   * @var float
   */
  public $profitRubDateByDate;
  /**
   * @var float
   */
  public $profitUsdDateByDate;
  /**
   * @var float
   */
  public $profitEurDateByDate;
  /**
   * @var float
   */
  public $rejectedProfitDateByDateRub;
  /**
   * @var float
   */
  public $rejectedProfitDateByDateUsd;
  /**
   * @var float
   */
  public $rejectedProfitDateByDateEur;
  /**
   * @var float
   */
  public $soldProfitDateByDateRub;
  /**
   * @var float
   */
  public $soldProfitDateByDateUsd;
  /**
   * @var float
   */
  public $soldProfitDateByDateEur;
  /**
   * @var int
   */
  public $cpaHits;
  /**
   * @var int
   */
  public $cpaTb;
  /**
   * @var int
   */
  public $sold;
  /**
   * @var int
   */
  public $soldVisible;
  /**
   * @var float
   */
  public $soldPartnerProfitRub;
  /**
   * @var float
   */
  public $soldPartnerProfitUsd;
  /**
   * @var float
   */
  public $soldPartnerProfitEur;
  /**
   * @var float
   */
  public $soldPriceRub;
  /**
   * @var float
   */
  public $soldPriceUsd;
  /**
   * @var float
   */
  public $soldPriceEur;
  /**
   * @var float
   */
  public $soldPartnerPriceRub;
  /**
   * @var float
   */
  public $soldPartnerPriceUsd;
  /**
   * @var float
   */
  public $soldPartnerPriceEur;
  /**
   * @var int
   */
  public $onetimeHits;
  /**
   * @var int
   */
  public $onetimeTb;
  /**
   * @var int
   */
  public $onetime;
  /**
   * @var int
   */
  public $visibleOnetime;
  /**
   * @var float
   */
  public $onetimeResellerProfitRub;
  /**
   * @var float
   */
  public $onetimeResellerProfitUsd;
  /**
   * @var float
   */
  public $onetimeResellerProfitEur;
  /**
   * @var float
   */
  public $onetimeProfitRub;
  /**
   * @var float
   */
  public $onetimeProfitUsd;
  /**
   * @var float
   */
  public $onetimeProfitEur;
  /**
   * @var integer
   */
  public $sellTbAccepted;
  /**
   * @var integer
   */
  public $soldTb;
  /**
   * @var float
   */
  public $soldTbProfitRub;
  /**
   * @var float
   */
  public $soldTbProfitUsd;
  /**
   * @var float
   */
  public $soldTbProfitEur;
  /**
   * @var integer
   */
  public $complains;
  /**
   * @var integer
   */
  public $calls;
  /**
   * @var integer
   */
  public $callsMno;
  /**
   * @var integer
   */
  public $complainAuto24;
  /**
   * @var integer
   */
  public $complainAutoMoment;
  /**
   * @var integer
   */
  public $complainAutoDuplicate;
}
