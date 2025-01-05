<?php
namespace mcms\support\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\support\models\Support;

class Ticket extends ApiResult
{
  protected $userId;
  protected $ticketId;

  public function init($params = [])
  {
    $this->userId = ArrayHelper::getValue($params, 'user_id', null);
    $this->ticketId = ArrayHelper::getValue($params, 'ticket_id', null);

    if (!$this->userId) $this->addError('user_id is not set');
    if (!$this->ticketId) $this->addError('ticket_id is not set');

  }

  public function getResult()
  {
    return Support::findOne([
      'id' => $this->ticketId,
      'created_by' => $this->userId
    ]);
  }


}