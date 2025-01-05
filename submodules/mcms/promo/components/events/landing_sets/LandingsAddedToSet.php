<?php
namespace mcms\promo\components\events\landing_sets;

use mcms\common\event\Event;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\LandingSet;
use Yii;
use yii\db\Query;

/**
 * Class LandingsAddedToSet
 * @package mcms\promo\components\events\landing_sets
 */
class LandingsAddedToSet extends Event
{
  public $landingSetItems;
  public $landingSet;

  /**
   * LandingsAddedToSet constructor.
   * @param LandingSet|null $landingSet
   * @param Query $landingSetItems
   */
  public function __construct(LandingSet $landingSet = null, $landingSetItems = null)
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
    return Yii::_t('promo.events.landings_added_to_set');
  }

  /**
   * @return array
   */
  public function getReplacements()
  {
    $itemStrs = [];
    foreach ($this->landingSetItems->each() as $item) {
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