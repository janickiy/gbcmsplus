<?php

namespace mcms\promo\components\events\ads_networks;

use Yii;
use mcms\common\event\Event;
use mcms\promo\models\AdsNetwork;

class AdsNetworkCreated extends Event
{

  /**
   * @var AdsNetwork
   */
  public $adsNetwork;

  /**
   * @param AdsNetwork|null $adsNetwork
   */
  public function __construct(AdsNetwork $adsNetwork = null)
  {
    $this->adsNetwork = $adsNetwork;
  }

  public function getModelId()
  {
    return $this->adsNetwork->id;
  }

  public function getEventName()
  {
    return Yii::_t('promo.events.ads_network_created');
  }

}
