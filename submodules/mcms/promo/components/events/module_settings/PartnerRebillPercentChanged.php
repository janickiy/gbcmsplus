<?php
namespace mcms\promo\components\events\module_settings;

use mcms\common\event\Event;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\Module;
use Yii;
use mcms\promo\models\module_settings\PartnerRebillPercentChanged as EventModel;

class PartnerRebillPercentChanged extends Event
{

  public $partnerSetting;

  public function __construct($oldSettings = null, $newSettings = null)
  {
    $this->partnerSetting = new EventModel([
      'oldRebillPercent' => ArrayHelper::getValue($oldSettings, Module::SETTINGS_MAIN_REBILL_PERCENT_FOR_PARTNER),
      'newRebillPercent' => ArrayHelper::getValue($newSettings, Module::SETTINGS_MAIN_REBILL_PERCENT_FOR_PARTNER),
    ]);
  }

  function getEventName()
  {
    return Yii::_t('promo.events.partner_rebill_percent_changed');
  }
}