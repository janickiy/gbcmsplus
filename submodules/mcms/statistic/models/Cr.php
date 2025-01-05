<?php
namespace mcms\statistic\models;

/**
 * Класс-модель CR=Conversion Rate. То есть соотношение например кол-ва подписок к кол-ву хитов.
 * Класс универсальный, а не только про хиты и подписки.
 * Возможно в будущем будет доработан форматтерами и применен во всех вьюхах где есть что-то про конверсии (ратио, CR)
 */
class Cr
{
  /** @var float Числитель (например кол-во подписок) */
  public $convertionsCount;
  /** @var float Знаменатель (например кол-во хитов) */
  public $fullCount;

  /**
   * @return float
   */
  public function getRate()
  {
    $this->convertionsCount = (float)$this->convertionsCount;
    $this->fullCount = (float)$this->fullCount;

    if (!$this->fullCount) {
      return 0;
    }
    return $this->convertionsCount / $this->fullCount;
  }
}
