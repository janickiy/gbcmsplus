<?php

namespace mcms\statistic\components\api;

use mcms\common\module\api\ApiResult;
use mcms\statistic\models\mysql\Referrals;
use mcms\statistic\Module;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class ReferralsApi
 * @package mcms\statistic\components\api
 */
class ReferralsApi extends ApiResult
{

  const DEFAULT_PAGE_SIZE = 100;

  /** @var \mcms\statistic\models\mysql\Referrals */
  private $model;
  private $requestData;

  private $result = null;

  /**
   * @inheritdoc
   */
  function init($params = [])
  {
    $this->requestData = ArrayHelper::getValue($params, 'requestData', []);
    $this->filterData($this->requestData);
    /** @var Module $statModule */
    $statModule = Yii::$app->getModule('statistic');
    $this->model = $statModule->getStatisticModel($this->requestData, Referrals::STATISTIC_NAME);
    $dataProvider = $this->model->getStatisticGroup();
    $dataProvider->setPagination(['pageSize' => self::DEFAULT_PAGE_SIZE]);
    $this->setDataProvider($dataProvider);
    $this->setResultTypeDataProvider();
  }

  /**
   * @return array
   */
  public function getResult()
  {
    if ($this->result === null) {
      $this->result = parent::getResult();
    }
    return [$this->model, $this->result];
  }
}