<?php

namespace mcms\payments\commands;

use mcms\payments\components\exchanger\ExchangerCurlException;
use mcms\payments\models\ExchangerCourse as ExchangerCourseModel;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

class ExchangerController extends Controller
{

  public $amount;
  public $currency;

  /**
   * Обновляет курсы
   */
  public function actionUpdate()
  {
    try {

      /** @var \mcms\payments\components\api\ExchangerCourses $exchangerApi */
      $exchangerApi = Yii::$app->getModule('payments')
        ->api('exchangerCourses', ['useCachedResults' => false]);

      $storeResult = ExchangerCourseModel::storeCurrencyCourses($exchangerApi->getCurrencyCourses());
      !$storeResult or ExchangerCourseModel::invalidateCache();
      $storeResult
        ? $this->stdout("Saved\n", Console::FG_GREEN)
        : $this->stdout("Save error\n", Console::FG_RED);

    } catch (ExchangerCurlException $e) {
      $this->stdout("Exchanger error: " . $e->getName() . " \n", Console::FG_RED);
    }
  }

  public function actionCourses()
  {
    /** @var \mcms\payments\components\api\ExchangerCourses $exchangerApi */
    $exchangerApi = Yii::$app->getModule('payments')
      ->api('exchangerPartnerCourses')
    ;

    $this->stdout(json_encode($exchangerApi->getCurrencyCourses()));
  }

  /**
   * Расчитывает курсы по --amount=<сумма> --currency=<eur|usd|rur>
   */
  public function actionTest()
  {

    $currency = $this->currency;
    $amount = $this->amount;

    if (!$currency || !$amount) {
      $this->stderr("Currency or amount not provided" . PHP_EOL, Console::FG_RED);
      exit();
    }

    /** @var \mcms\payments\components\api\ExchangerPartnerCourses $exchangerApi */
    $exchangerApi = Yii::$app->getModule('payments')
      ->api('exchangerPartnerCourses', ['useCachedResults' => false])
    ;

    foreach ($this->convert($exchangerApi, $currency, $amount) as $c => $v) {
      $this->stdout(sprintf("[%s] = %s\n", $c, $v));
    }
  }

  private function convert(\mcms\payments\components\api\ExchangerPartnerCourses $api, $currency, $amount)
  {
    switch ($currency) {
      case 'eur':
        $result = $api->fromEur($amount);
        $this->stdout('From EUR:' . PHP_EOL);
        break;
      case 'usd':
        $this->stdout('From USD:' . PHP_EOL);
        $result = $api->fromUsd($amount);
        break;
      case 'rur':
        $this->stdout('From RUB:' . PHP_EOL);
        $result = $api->fromRub($amount);
        break;
      default :
        $this->stderr('Currency ' . $currency . ' not found');
        exit();
    }
    return $result;
  }

  /**
   * @inheritDoc
   */
  public function options($actionID)
  {
    return array_merge(parent::options($actionID),
      $actionID == 'test' ? ['amount', 'currency'] : []
    );
  }

  /**
   * Конвертирует для теста по 100 евро руб и доллары
   */
  public function actionTest100()
  {
    /** @var \mcms\payments\components\api\ExchangerPartnerCourses $exchangerApi */
    $exchangerApi = Yii::$app->getModule('payments')
      ->api('exchangerParnterCourses', ['useCachedResults' => false])
    ;

    $result = [
      'rur' => $this->convert($exchangerApi, 'rur', 100),
      'eur' => $this->convert($exchangerApi, 'eur', 100),
      'usd' => $this->convert($exchangerApi, 'usd', 100),
    ];
    print_r($result);
  }

}