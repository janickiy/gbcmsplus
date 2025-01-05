<?php
namespace mcms\support\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\support\models\Support;
use yii\web\ForbiddenHttpException;

class TicketClose extends ApiResult
{
  protected $userId;
  protected $tickerId;
  /**
   * @var Support
   */
  protected $ticket;

  public function init($params = [])
  {
    $this->tickerId = ArrayHelper::getValue($params, 'ticket_id');
    $this->userId = ArrayHelper::getValue($params, 'user_id');
  }

  public function getResult()
  {
    /* @var $ticket Support */
    $ticket = $this->getTicket();

    if (!$ticket) return false;

    if(!$ticket->canManageOwnTicket()) throw new ForbiddenHttpException('Access denied');

    $ticket->close();

    return $ticket->save();
  }

  public function getTicket()
  {
    if (!$this->ticket) {
      $this->ticket = Support::find()->where([
        'id' => $this->tickerId,
        'created_by' => $this->userId
      ])->one();
    }

    return $this->ticket;
  }
}