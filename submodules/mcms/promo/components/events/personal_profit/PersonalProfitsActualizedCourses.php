<?php
namespace mcms\promo\components\events\personal_profit;

use mcms\common\event\Event;
use mcms\payments\components\exchanger\CurrencyCourses;
use Yii;

/**
 * Кидаем это событие при обновлении фикс. цпа согласно актуальным курсам
 * Сделано чтобы можно было в логе увидеть
 */
class PersonalProfitsActualizedCourses extends Event
{

  /** @var string|CurrencyCourses курсы валют в json */
  public $exchangeCourses;
  /** @var int */
  public $programId;

  /**
   * @return string
   */
  public function getEventName()
  {
    return Yii::_t('promo.events.personal-profit-actualized_courses');
  }
}
