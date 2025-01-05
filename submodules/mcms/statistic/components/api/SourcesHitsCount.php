<?php

namespace mcms\statistic\components\api;


use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\statistic\components\mainStat\BaseFetch;
use mcms\statistic\components\mainStat\FormModel;
use mcms\statistic\components\mainStat\Group;
use Yii;

/**
 * Class SourcesHitsCount
 * @package mcms\statistic\components\api
 */
class SourcesHitsCount extends ApiResult
{

  public $sourceId;
  public $timeFrom;
  public $timeTo;

  /**
   * @inheritdoc
   */
  function init($params = [])
  {
    $this->sourceId = ArrayHelper::getValue($params, 'sourceId');
    $this->timeFrom = ArrayHelper::getValue($params, 'timeFrom');
    $this->timeTo = ArrayHelper::getValue($params, 'timeTo', time());
  }

  /**
   * @return double
   */
  public function getResult()
  {
    $formModel = new FormModel([
      'sources' => $this->sourceId,
      'dateFrom' => date('Y-m-d', $this->timeFrom),
      'dateTo' => date('Y-m-d', $this->timeTo),
      'groups' => [Group::BY_DATES],
      'currency' => null,
    ]);

    /* @var BaseFetch $fetch */
    $fetch = Yii::$container->get(BaseFetch::class, [$formModel]);

    return $fetch->getDataProvider()->footerRow->getHits();
  }


}