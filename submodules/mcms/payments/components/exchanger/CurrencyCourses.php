<?php

namespace mcms\payments\components\exchanger;

use mcms\payments\models\ExchangerCourse;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class CurrencyCourses implements \JsonSerializable
{

  const USD_RUR = 'usd_rur';
  const RUR_USD = 'rur_usd';
  const USD_EUR = 'usd_eur';
  const EUR_USD = 'eur_usd';
  const EUR_RUR = 'eur_rur';
  const RUR_EUR = 'rur_eur';

  const USD = 'usd';
  const EUR = 'eur';
  const RUB = 'rub';

  public $usd_rur;
  public $rur_usd;
  public $usd_eur;
  public $eur_usd;
  public $eur_rur;
  public $rur_eur;
  public $usd_rur_real;
  public $rur_usd_real;
  public $usd_eur_real;
  public $eur_usd_real;
  public $eur_rur_real;
  public $rur_eur_real;
  public $usd_rur_partner;
  public $rur_usd_partner;
  public $usd_eur_partner;
  public $eur_usd_partner;
  public $eur_rur_partner;
  public $rur_eur_partner;

  private $mapping = [];

  /**
   * @param array $mapping
   * @return $this
   */
  public function setMapping(array $mapping)
  {
    $this->mapping = $mapping;
    return $this;
  }

  public function setDirectionCourse($direction, $course)
  {
    if (!empty($this->mapping)) return $this->setDirectionCourseWithMapping($direction, $course);
    return $this->setProperty($direction, $course);
  }

  private function setProperty($property, $value)
  {
    if (property_exists($this, $property)) {
      $this->{$property} = $value;
    }

    return $this;
  }

  private function setDirectionCourseWithMapping($direction, $course)
  {
    $field = ArrayHelper::getValue($this->mapping, $direction);
    return $this->setProperty($field, $course);
  }

  /**
   * @param ExchangerCourse $model
   * @return CurrencyCourses
   */
  public static function load(ExchangerCourse $model)
  {
    $instance = new self;
    $instance->usd_rur = (float)$model->usd_rur;
    $instance->rur_usd = (float)$model->rur_usd;
    $instance->usd_eur = (float)$model->usd_eur;
    $instance->eur_usd = (float)$model->eur_usd;
    $instance->eur_rur = (float)$model->eur_rur;
    $instance->rur_eur = (float)$model->rur_eur;

    $instance->usd_rur_real = (float)$model->usd_rur_real;
    $instance->rur_usd_real = (float)$model->rur_usd_real;
    $instance->usd_eur_real = (float)$model->usd_eur_real;
    $instance->eur_usd_real = (float)$model->eur_usd_real;
    $instance->eur_rur_real = (float)$model->eur_rur_real;
    $instance->rur_eur_real = (float)$model->rur_eur_real;

    $instance->usd_rur_partner = (float)$model->usd_rur_partner;
    $instance->rur_usd_partner = (float)$model->rur_usd_partner;
    $instance->usd_eur_partner = (float)$model->usd_eur_partner;
    $instance->eur_usd_partner = (float)$model->eur_usd_partner;
    $instance->eur_rur_partner = (float)$model->eur_rur_partner;
    $instance->rur_eur_partner = (float)$model->rur_eur_partner;

    return $instance;
  }

  /**
   * @param ExchangerCourse $model
   * @return CurrencyCourses
   */
  public static function loadPartner(ExchangerCourse $model)
  {
    $instance = new self;
    $instance->usd_rur = (float)$model->usd_rur_partner;
    $instance->rur_usd = (float)$model->rur_usd_partner;
    $instance->usd_eur = (float)$model->usd_eur_partner;
    $instance->eur_usd = (float)$model->eur_usd_partner;
    $instance->eur_rur = (float)$model->eur_rur_partner;
    $instance->rur_eur = (float)$model->rur_eur_partner;

    return $instance;
  }

  /**
   * Конвертация
   * @param $sum
   * @param string $fromCurrency Валюта для продажи
   * @return array
   * @throws UnknownCurrencyException
   */
  public function convert($sum, $fromCurrency = self::RUB)
  {
    switch ($fromCurrency) {
      case self::RUB:
        return [
          self::RUB => $sum,
          self::USD => $sum * $this->rur_usd,
          self::EUR => $sum * $this->rur_eur
        ];
        break;
      case self::USD:
        return [
          self::RUB => $sum * $this->usd_rur,
          self::USD => $sum,
          self::EUR => $sum * $this->usd_eur
        ];
        break;
      case self::EUR:
        return [
          self::RUB => $sum * $this->eur_rur,
          self::USD => $sum * $this->eur_usd,
          self::EUR => $sum,
        ];
        break;
    }


    $e = new UnknownCurrencyException();
    $e->currency = $fromCurrency;
    throw $e;
  }

  /**
   * Обратная конвертация
   * Получить сумму, которая была до конвертации через @see convert()
   * TRICKY Метод возвращает приблизительный результат, так как курс, по которому была произведена конвертация мог обновиться
   * TRICKY Метод возвращает приблизительный результат, так как PHP не гарантирует точный результат при работе с дробными числами.
   * Для точных вычислений нужно использовать математические плагины PHP
   *
   * Пример использования метода:
   * ```
   * // Исходная сумма 100 рублей
   * $sourceRub = 100;
   *
   * // После конвертации получается 1.5 доллара
   * $convertRubToUsd = convert($sourceRub, 'RUB')['USD'];
   *
   * // Пробуем узнать сколько было рублей до конвертации в доллары
   * // В результате мы не получим ожидаемой суммы, так как курсы покупки и продажи различаются
   * // В первом случае мы продавали рубли и покупали доллары, в примере ниже мы продаем доллары и покупаем рубли
   * $convertUsdToRubFail = convert($convertRubToUsd, 'USD')['RUB']; // 95 RUB
   *
   * // Что бы получить правильный результат, метод reverseConvert использует тот же курс обмена,
   * // который использовался бы при покупке долларов за рубли, в отличии от convert(), который вернет результат по
   * // курсу продажи долларов и покупки рублей
   * $convertUsdToRubSuccess = reverseConvert($convertRubToUsd, 'USD')['RUB']; // 100 RUB
   * ```
   *
   * @param $sum
   * @param string $currency
   * @return array
   * @throws UnknownCurrencyException
   */
  public function reverseConvert($sum, $currency = self::RUB)
  {
    switch ($currency) {
      case self::RUB:
        return [
          self::RUB => $sum,
          self::USD => $sum / $this->usd_rur,
          self::EUR => $sum / $this->eur_rur
        ];
        break;
      case self::USD:
        return [
          self::RUB => $sum / $this->rur_usd,
          self::USD => $sum,
          self::EUR => $sum / $this->eur_usd
        ];
        break;
      case self::EUR:
        return [
          self::RUB => $sum / $this->rur_eur,
          self::USD => $sum / $this->usd_eur,
          self::EUR => $sum,
        ];
        break;
    }


    $e = new UnknownCurrencyException();
    $e->currency = $currency;
    throw $e;
  }

  public function fromRub($sum)
  {
    return $this->convert($sum, self::RUB);
  }

  public function fromUsd($sum)
  {
    return $this->convert($sum, self::USD);
  }

  public function fromEur($sum)
  {
    return $this->convert($sum, self::EUR);
  }

  function jsonSerialize()
  {
    $jsonData = [];
    foreach (['usd_rur', 'rur_usd', 'usd_eur', 'eur_usd', 'eur_rur', 'rur_eur'] as $fieldName) {
      $jsonData[$fieldName] = $this->{$fieldName};
    }

    return $jsonData;
  }

  /**
   * Курс обмена
   * @param string $sourceCourse в нижнем регистре rub|rur|usd|eur
   * @param string $course в нижнем регистре rub|rur|usd|eur
   * @return number
   * @throws Exception
   */
  public function course($sourceCourse, $course)
  {
    if ($sourceCourse == 'rub') $sourceCourse = 'rur';
    if ($course == 'rub') $course = 'rur';

    $fieldName = $sourceCourse . '_' . $course;
    if (!isset($this->{$fieldName})) throw new Exception("Не удалось найти курс для обмена $sourceCourse в $course");

    return $this->{$fieldName};
  }
}