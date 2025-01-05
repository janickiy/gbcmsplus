<?php

namespace mcms\promo\components\landing_sets;

use mcms\promo\models\LandingSet;
use mcms\promo\models\LandingSetItem;
use yii\base\Object;
use yii\db\ActiveQuery;


/**
 * Class LandingSetsLandsUpdater
 * @package mcms\promo\components\landing_sets
 */
class LandingSetsLandsUpdater extends Object
{

  /** @var  array Если задано, то обновлены будут только те лендинг-сеты, у которых есть указанные ленды */
  public $landingIds = [];

  public function run()
  {
    foreach ($this->getSetsQuery()->each() as $landingSet) {
      (new LandingSetLandsUpdater($landingSet))->run();
    }
  }

  /**
   * @return ActiveQuery
   */
  private function getSetsQuery()
  {
    $setsQuery = LandingSet::find();

    if (!empty($this->landingIds)) {
      $setsQuery->joinWith('items')->where([LandingSetItem::tableName() . '.landing_id' => $this->landingIds]);
    }

    return $setsQuery;
  }

}
