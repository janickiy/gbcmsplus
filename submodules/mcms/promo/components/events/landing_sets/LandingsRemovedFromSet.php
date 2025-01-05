<?php
namespace mcms\promo\components\events\landing_sets;

use mcms\common\event\Event;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\LandingSet;
use mcms\promo\models\LandingSetItem;
use Yii;

/**
 * Class LandingsRemovedFromSet
 * @package mcms\promo\components\events\landing_sets
 */
class LandingsRemovedFromSet extends Event
{
  public $landingSetItems;
  public $landingSet;

  /**
   * LandingsRemovedFromSet constructor.
   * @param LandingSet|null $landingSet
   * @param LandingSetItem[]|null $landingSetItems
   */
  public function __construct(LandingSet $landingSet = null, array $landingSetItems = null)
  {
    $this->landingSet = $landingSet;
    $this->landingSetItems = $landingSetItems;
  }

  /**
   * @return int
   */
  public function getModelId()
  {
    return $this->landingSet->id;
  }

  /**
   * @inheritdoc
   */
  function getEventName()
  {
    return Yii::_t('promo.events.landings_removed_from_set');
  }

  /**
   * @return array
   */
  public function getReplacements()
  {
    $itemStrs = [];
    foreach ($this->landingSetItems as $item) {
      // пример: #123 LandingName (Megafon)
      $itemStrs[] = $item->landing->getStringInfo() . ' (' . $item->operator->getStringInfo() . ')';
    }

    return ArrayHelper::merge(parent::getReplacements(), [
      '{landingSetItems}' => implode('<br />', $itemStrs),
    ]);
  }

  /**
   * @inheritdoc
   */
  public static function getUrl($id = null)
  {
    return ['/promo/landing-sets/update/', 'id' => $id];
  }

  /**
   * @inheritdoc
   */
  public function getReplacementsHelp()
  {
    return array_merge(parent::getReplacementsHelp(), [
      '{landingSetItems}' => LandingSet::translate('landings')
    ]);
  }
}