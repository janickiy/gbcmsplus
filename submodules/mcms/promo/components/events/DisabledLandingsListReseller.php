<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use Yii;

class DisabledLandingsListReseller extends Event
{

  public $landings;

  public function __construct($landings = null)
  {
    $this->landings = $landings;
    $this->allowSerialization = false;
  }

  public function getReplacements()
  {
    $template = ':landingId. :landingName - :operators';

    $operatorsByLanding = [];
    foreach ($this->landings as $landingId => $operators) {
      foreach ($operators as $landingOperator) {
        $operator = sprintf('%s (%s)',
          $landingOperator->operator->name,
          $landingOperator->operator->country->code
        );
        $operatorsByLanding[$landingId][] = $operator;
      }
    }

    $landings = [];
    foreach ($this->landings as $landingId => $operators) {
      foreach ($operators as $landingOperator) {
        $landings[$landingId] = strtr($template, [
          ':landingId' => $landingId,
          ':landingName' => $landingOperator->landing->name,
          ':operators' => implode(', ', $operatorsByLanding[$landingId])
        ]);
      }
    }

    return array_merge(parent::getReplacements(), [
      '{landings}' => implode('<br />', $landings)
    ]);
  }

  public static function getUrl($id = null)
  {
    return ['/promo/landings/index'];
  }

  public function labels()
  {
    return [
      'landings' => Yii::_t('promo.events.landing_list')
    ];
  }

  function getEventName()
  {
    return Yii::_t('promo.events.disabled_landing_list_replaced_reseller');
  }
}