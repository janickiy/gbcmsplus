<?php
namespace mcms\promo\components\events\module_settings;

use mcms\common\event\Event;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\Module;
use Yii;
use mcms\promo\models\module_settings\PartnerBuyoutPercentChanged as EventModel;

class PartnerBuyoutPercentChanged extends Event
{

  public $partnerSetting;

  public function __construct($oldSettings = null, $newSettings = null)
  {
    $this->partnerSetting = new EventModel([
      'oldBuyoutPercent' => ArrayHelper::getValue($oldSettings, Module::SETTINGS_MAIN_BUYOUT_PERCENT_FOR_PARTNER),
      'newBuyoutPercent' => ArrayHelper::getValue($newSettings, Module::SETTINGS_MAIN_BUYOUT_PERCENT_FOR_PARTNER),
    ]);
  }

  function getEventName()
  {
    return Yii::_t('promo.events.partner_buyout_percent_changed');
  }
}