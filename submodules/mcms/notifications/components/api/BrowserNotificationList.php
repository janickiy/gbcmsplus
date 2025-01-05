<?php
namespace mcms\notifications\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\notifications\models\search\BrowserNotificationSearch;

class BrowserNotificationList extends ApiResult
{
  public function init($params = [])
  {
    $this->setResultTypeDataProvider();
    $this->prepareDataProvider(new BrowserNotificationSearch(), $params);
  }


}