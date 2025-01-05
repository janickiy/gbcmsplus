<?php

namespace mcms\statistic\components\api;


use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\statistic\components\mainStat\BaseFetch;
use mcms\statistic\components\mainStat\FormModel;
use mcms\statistic\components\mainStat\Group;
use mcms\statistic\components\mainStat\mysql\Row;
use Yii;

/**
 * Class SourcesLandingsConvert
 * @package mcms\statistic\components\api
 */
class SourcesLandingsConvert extends ApiResult
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
   * @return array
   */
  public function getResult()
  {
    $formModel = new FormModel([
      'dateFrom' => date('Y-m-d', $this->timeFrom),
      'dateTo' => date('Y-m-d', $this->timeTo),
      'sources' => $this->sourceId,
      'groups' => [Group::BY_LANDINGS],
      'currency' => null,
    ]);
    /* @var BaseFetch $fetch */
    $fetch = Yii::$container->get(BaseFetch::class, [$formModel]);

    $converts = [];
    foreach ($fetch->getDataProvider()->getModels() as $row) {
      /** @var $row Row */
      $countHits = $row->getHits();

      if (!$countHits) {
        $converts[$row->getGroup()] = 0;
        continue;
      }

      $converts[$row->getGroup()] = ($row->getOns() + $row->getOnetime() + $row->getSold()) / $countHits;
    }

    return $converts;
  }

}