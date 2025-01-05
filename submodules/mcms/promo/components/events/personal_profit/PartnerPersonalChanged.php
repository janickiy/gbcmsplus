<?php
namespace mcms\promo\components\events\personal_profit;

use mcms\common\event\Event;
use Yii;
use mcms\promo\models\PersonalProfit;

/**
 * Class PartnerPersonalChanged
 * @package mcms\promo\components\events\personal_profit
 */
class PartnerPersonalChanged extends Event
{
  /**
   * @var PersonalProfit
   */
  public $personalProfit;

  /**
   * @param PersonalProfit|null $personalProfit
   */
  public function __construct(PersonalProfit $personalProfit = null)
  {
    $this->personalProfit = $personalProfit;
  }

  /**
   * @return string
   */
  function getEventName()
  {
    return Yii::_t('promo.events.personal-profit-partner_personal_changed');
  }
}