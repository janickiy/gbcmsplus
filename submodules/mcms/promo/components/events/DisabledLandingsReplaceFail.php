<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use mcms\promo\models\Landing;
use mcms\promo\models\Source;
use mcms\user\models\User;
use Yii;

class DisabledLandingsReplaceFail extends Event
{
  public $landingFrom;
  public $landings;
  public $user;
  public $source;

  public function __construct(Landing $landingFrom = null, Source $source = null, User $user = null, $landings = null)
  {
    $this->landingFrom = $landingFrom;
    $this->landings = $landings;
    $this->user = $user;
    $this->source = $source;
    $this->allowSerialization = false;
  }

  public function getOwner()
  {
    return $this->user;
  }

  function getEventName()
  {
    return Yii::_t('promo.events.disabled_landing_replace_failed');
  }

  public static function getUrl($id = null)
  {
    return ['/partners/links/add/', 'id' => $id, '#' => 'step_2'];
  }

  public function getModelId()
  {
    return $this->source->id;
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