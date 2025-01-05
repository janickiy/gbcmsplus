<?php

namespace mcms\statistic\models;


use yii\base\Model;

/**
 * Модель для статистики реселлера, которая выгружается в Эксельке.
 * Возможно позже будет внедрена и в интерфейс в каком-либо виде.
 *
 * Поля, которые берутся из статы реселлера начинаются с $res. Для инвестора начинаются с $i
 * Например $resRevAccepted и $iRevAccepted
 *
 * Class ResellerStatisticModel
 * @package mcms\statistic\models
 */
class ResellerStatisticModel extends Model
{
  public $date;
  public $countryCode;
  public $operator;
  public $user;
  public $currency;

  public $resHits = 0;
  public $resUniques = 0;
  public $resTb = 0;
  public $resAccepted = 0;

  public $iSubs = 0;
  public $iOffs = 0;
  public $iOffs24 = 0;
  public $iRebills = 0;
  public $iRebillsOnDate = 0;
  public $iProfitOnDate = 0;
  public $iTotalSum = 0;
  public $iBuyoutSum = 0;

  public $resRevAccepted = 0;
  public $resCpaAccepted = 0;
  public $resOnetimes = 0;
  public $resSold = 0;
  public $resComplains = 0;
  public $resCalls = 0;
  public $resRevResSum = 0;
  public $resRevPartnerSum = 0;
  public $resOffs = 0;
  public $resOffs24 = 0;
  public $resRebills = 0;
  public $resRebillsOnDate = 0;
  public $resProfitOnDate = 0;
  public $resOnetimeResSum = 0;
  public $resOnetimePartnerSum = 0;
  public $resSubs = 0;
  public $resVisibleSubscriptions = 0;

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'date' => 'Date',
      'countryCode' => 'Geo',
      'operator' => 'Carrier',
      'user' => 'User',
      'currency' => 'Currency',

      'resHits' => 'Hits',
      'resUniques' => 'Uniques',
      'resTb' => 'TB',
      'resAccepted' => 'Accepted',
      'resOffs24' => 'Revsh. Unsubscribed 24h',
      'resOffs' => 'Revsh. Unsubscribed',
      'resRebills' => 'Revsh. Charges',
      'resRevResSum' => 'Revsh. Reseller sum',

      'iSubs' => 'Subscribed',
      'iOffs' => 'Unsubscribed',
      'iOffs24' => 'Unsubscribed 24h',
      'iRebills' => 'Charges',
      'iRebillsOnDate' => 'Charges on date',
      'iProfitOnDate' => 'Profit on date',
      'iTotalSum' => 'Total profit',
      'iBuyoutSum' => 'Investor buyout',

      'resRevAccepted' => 'Revsh. accepted',
      'resCpaAccepted' => 'CPA Accepted',
      'resOnetimes' => 'IK',
      'resSold' => 'Sold',
      'resComplains' => 'Count complains',
      'resCalls' => 'Count calls',
      'resRevPartnerSum' => 'Revsh. Sum',
      'resRebillsOnDate' => 'Revsh. Charges on date',
      'resProfitOnDate' => 'Revsh. Profit on date',
      'resOnetimeResSum' => '[RES] IK Reseller sum',
      'resOnetimePartnerSum' => '[RES] IK Partner sum',
      'resSubs' => 'Revsh. Subscribed',
      'resVisibleSubscriptions' => 'Vis. subs.',
    ];
  }


}