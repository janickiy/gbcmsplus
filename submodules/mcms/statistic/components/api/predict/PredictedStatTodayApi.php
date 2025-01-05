<?php

namespace mcms\statistic\components\api\predict;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\statistic\Module;
use Yii;

/**
 * Class PredictedStatToday
 * @package mcms\statistic\components\api\predict
 */
class PredictedStatTodayApi extends ApiResult
{
  private $models;

  /**
   * @param array $params
   */
  function init($params = [])
  {
    $this->models = ArrayHelper::getValue($params, 'models', []);
  }

  /**
   * @return array
   */
  public function getResult()
  {
    $calc = (new PredictedStatTodayCalc(
      $this->models,
      $this->getModule()->getSampleDaysCount()
    ));

    return $calc->getPredictions();
  }

  /**
   * @return Module
   */
  private function getModule()
  {
    return Yii::$app->getModule('statistic');
  }
}
