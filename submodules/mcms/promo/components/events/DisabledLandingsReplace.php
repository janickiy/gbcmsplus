<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use mcms\promo\models\Landing;
use mcms\user\models\User;
use Yii;

class DisabledLandingsReplace extends Event
{

  public $landingFrom;
  public $user;
  public $landings;

  public function __construct(Landing $landingFrom = null, User $user = null, $landings = null)
  {
    $this->landingFrom = $landingFrom;
    $this->landings = $landings;
    $this->user = $user;
    $this->allowSerialization = false;
  }

  public function getOwner()
  {
    return $this->user;
  }

  function getEventName()
  {
    return Yii::_t('promo.events.disabled_landing_replaced');
  }

  public function getModelId()
  {
    return $this->landingFrom->id;
  }

  public static function getUrl($id = null)
  {
    return ['/partners/links/index'];
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
}