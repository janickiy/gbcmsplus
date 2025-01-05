<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use mcms\user\models\User;
use Yii;

/**
 * Событие, вызываемое при выключении выкупа для партнера
 */
class SourceOperatorLandingsChangeProfitType extends Event
{
  /**
   * SourceOperatorLandingsChangeProfitType constructor.
   * @param User $partner
   */
  public function __construct(User $partner = null)
  {
    $this->setOwner($partner);
  }

  /**
   * @inheritdoc
   */
  public static function getUrl($id = null)
  {
    return ['/partners/links/index/'];
  }

  /**
   * @return string
   */
  function getEventName()
  {
    return Yii::_t('promo.events.changed_profit_type');
  }
}