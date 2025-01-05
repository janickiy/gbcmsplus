<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use mcms\promo\models\Landing;
use Yii;

class DisabledLandingsReseller extends Event
{

  public $landingFrom;
  public $landings;

  public function __construct(Landing $landingFrom = null, $landings = null)
  {
    $this->landingFrom = $landingFrom;
    $this->landings = $landings;
    $this->allowSerialization = false;
  }

  function getEventName()
  {
    return Yii::_t('promo.events.disabled_landing_replaced_reseller');
  }

  public function getModelId()
  {
    return $this->landingFrom->id;
  }

  public function getReplacements()
  {
    $operators = [];
    foreach ($this->landings as $landingOperator) {
      $operators[] = sprintf('%s (%s)',
        $landingOperator->operator->name,
        $landingOperator->operator->country->code
      );
    }

    return array_merge(parent::getReplacements(), [
      '{landingFrom.operatorNames}' => '',
      '{landingFrom.countryCodes}' => implode(', ', $operators),
    ]);
  }

  public static function getUrl($id = null)
  {
    return ['/promo/landings/view', 'id' => $id];
  }
}