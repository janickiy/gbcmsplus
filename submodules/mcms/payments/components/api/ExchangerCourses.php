<?php

namespace mcms\payments\components\api;

use mcms\common\module\api\ApiResult;
use mcms\payments\components\exchanger\CurrencyCourses;
use mcms\payments\models\ExchangerCourse;
use mcms\payments\models\ExchangerCourse as ExchangerCourseModel;
use Yii;
use yii\helpers\ArrayHelper;

class ExchangerCourses extends ApiResult
{

  protected $useCachedResults;

  /** @var  \mcms\payments\components\exchanger\CurrencyCourses */
  protected $currencyCourses;

  public function init($params = [])
  {
    $this->useCachedResults = ArrayHelper::getValue($params, 'useCachedResults', true);

    $this->currencyCourses = !$this->useCachedResults
      ? $this->getUnCachedCurrencyCourses()
      : $this->getCachedCurrencyCourses()
      ;

    if ($this->useCachedResults && $this->currencyCourses === null) {
      $this->currencyCourses = $this->getUnCachedCurrencyCourses();
    }
  }

  /**
   * @return \mcms\payments\components\exchanger\CurrencyCourses
   */
  public function getCurrencyCourses()
  {
    return $this->currencyCourses;
  }

  /**
   * @return \mcms\payments\components\exchanger\CurrencyCourses|null
   * @throws \yii\base\InvalidConfigException
   */
  protected function getCachedCurrencyCourses()
  {
    $cachedResults = ExchangerCourse::getCurrencyCourses();
    if ($cachedResults !== null) {
      return $cachedResults;
    }

    $courses = $this->getUnCachedCurrencyCourses();

    return ExchangerCourseModel::storeCurrencyCourses($courses);
  }

  /**
   * @return mixed
   * @throws \yii\base\InvalidConfigException
   */
  protected function getUnCachedCurrencyCourses()
  {
    /** @var \mcms\payments\Module $module */
    $module = Yii::$app->getModule('payments');
    return Yii::createObject($module->getExchangerSourceclass)->getExchangerCourses();
  }

  /**
   * @return \mcms\payments\components\exchanger\CurrencyCourses|null
   * @throws \yii\base\InvalidConfigException
   */
  protected function getCachedPartnerCurrencyCourses()
  {
    $cachedResults = ExchangerCourse::getPartnerCurrencyCourses();
    if ($cachedResults !== null) {
      return $cachedResults;
    }

    $courses = $this->getUnCachedCurrencyCourses();
    $model = ExchangerCourseModel::storeCurrencyCourses($courses);

    return CurrencyCourses::loadPartner($model);
  }

  public function fromRub($sum)
  {
    return $this->currencyCourses->fromRub($sum);
  }

  public function fromUsd($sum)
  {
    return $this->currencyCourses->fromUsd($sum);
  }

  public function fromEur($sum)
  {
    return $this->currencyCourses->fromEur($sum);
  }

  public function fromCourse($course, $sum)
  {
    return $this->currencyCourses->convert($sum, $course);
  }

  /**
   * Обратная конвертация
   * TRICKY Метод назван reverseConvert, а не fromCourse, что бы случайно не перепутать их
   * @param $course
   * @param $sum
   * @return array
   */
  public function reverseConvert($course, $sum)
  {
    return $this->currencyCourses->reverseConvert($sum, $course);
  }

  /**
   * Курс обмена
   * @param $sourceCurrency
   * @param $currency
   * @return number
   */
  public function course($sourceCurrency, $currency)
  {
    return $this->currencyCourses->course($sourceCurrency, $currency);
  }
}