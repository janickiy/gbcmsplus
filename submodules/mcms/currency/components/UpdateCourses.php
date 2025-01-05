<?php
namespace mcms\currency\components;

use mcms\currency\models\Currency;
use mcms\promo\components\ApiHandlersHelper;
use rgk\exchange\components\Currencies;
use rgk\utils\components\output\ConsoleOutput;
use rgk\utils\components\output\OutputInterface;
use rgk\utils\interfaces\ExecutableInterface;
use Yii;

/**
 * Компонент для обновления курсов валют и сохранения истории курсов
 */
class UpdateCourses implements ExecutableInterface
{
  /** @var  OutputInterface */
  private $_logger;

  public function __construct()
  {
    $this->setLogger(new ConsoleOutput()); // логгер по-умолчанию в консоль
  }

  public function execute()
  {
    $this->log('Log creation begin');
    Currency::createoldCoursesLog();
    $this->log('Log creation complete');

    $this->log('Update courses begin');
    $this->updateCourses();
    $this->log('Update courses complete');

    // Сбрасываем кеш на микросервисах
    ApiHandlersHelper::clearCache(Currency::MS_CACHE_KEY);
  }

  /**
   * Обновление курсов валют
   */
  private function updateCourses()
  {
    /** @var Currencies $apiCurrencies */
    $apiCurrencies = Yii::$app->exchange->getCurrencies();

    foreach ($apiCurrencies as $apicurrency) {
      /** @var \rgk\exchange\models\Currency $apicurrency */

      $currency = Currency::findOne(['code' => $apicurrency->getCode3l()]);
      if ($currency === null) {
        $currency = new Currency([
          'code' => $apicurrency->getCode3l(),
          'name' => serialize(['ru' => strtoupper($apicurrency->getCode3l()), 'en' => strtoupper($apicurrency->getCode3l())]), // TODO: Название валюты не приходит
        ]);
      }
      $currency->setScenario(Currency::SCENARIO_SYNC);

      $currency->to_rub = $apicurrency->getToRub();
      $currency->to_usd = $apicurrency->getToUsd();
      $currency->to_eur = $apicurrency->getToEur();

      if (!$currency->save()) {
        $this->log('Currency course updating error. ' . $apicurrency->getCode3l());
        Yii::error(sprintf(
          "Обновить курс для валюты {$apicurrency->getCode3l()} не удалось:\n%s\nАттрибуты модели:\n%s",
          json_encode($currency->getErrors()),
          json_encode($currency->getAttributes())
        ), __METHOD__);
      }
    }
  }

  /**
   * @param OutputInterface $logger
   * @return $this
   */
  public function setLogger(OutputInterface $logger)
  {
    $this->_logger = $logger;
    return $this;
  }

  /**
   * @param $message
   */
  protected function log($message)
  {
    $this->_logger->log(date('H:i:s') . ': ' . $message . "\n\n");
  }
}