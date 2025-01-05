<?php
namespace mcms\promo\components\events;

use mcms\common\helpers\ArrayHelper;
use mcms\common\event\Event;
use mcms\promo\models\Operator;
use Yii;
use mcms\promo\models\Landing;

class LandingListCreatedReseller extends Event
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

    $landings = array_map(function(Landing $landing) use ($template) {
      $operators = array_map(function(Operator $landingOperator) {
        return sprintf('%s (%s)', $landingOperator->name, $landingOperator->country->code);
      }, $landing->operator);

      return strtr($template, [
        ':landingId' => $landing->id,
        ':landingName' => $landing->name,
        ':operators' => implode(', ', $operators)
      ]);
    }, $this->landings);

    return ArrayHelper::merge(parent::getReplacements(), [
      '{landings}' => implode('<br />', $landings),
    ]);
  }

  public function getReplacementsHelp()
  {
    return ArrayHelper::merge(parent::getReplacementsHelp(), [
      '{landings}' => Yii::_t('promo.events.landing_list'),
    ]);
  }

  public static function getUrl($id = null)
  {
    return ['/promo/landings/index'];
  }

  function getEventName()
  {
    return Yii::_t('promo.events.landing_list_created_res');
  }
}