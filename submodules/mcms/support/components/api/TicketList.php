<?php
namespace mcms\support\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\support\models\search\SupportSearch;

class TicketList extends ApiResult
{
  protected $userId;

  public function init($params = [])
  {
    $this->userId = ArrayHelper::getValue($params, 'created_by');
    if (!$this->userId) $this->addError('user_id is not set');

    $this->setResultTypeDataProvider();
    $this->prepareDataProvider(new SupportSearch(), $params);
  }

}