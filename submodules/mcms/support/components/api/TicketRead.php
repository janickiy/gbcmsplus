<?php
namespace mcms\support\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\support\models\Support;
use yii\web\ForbiddenHttpException;

class TicketRead extends ApiResult
{

  protected $ticketId;

  public function init($params = [])
  {
    $this->ticketId = ArrayHelper::getValue($params, 'ticketId');
  }

  public function getResult()
  {
    /* @var $ticket Support */
    $ticket = Support::find()->where([
      'id' => $this->ticketId
    ])->one();

    if(!$ticket->canManageOwnTicket()) throw new ForbiddenHttpException('Access denied');

    $ticket->scenario = Support::SCENARIO_OWNER_SET_AS_READ;
    if (!$ticket) return false;
    $ticket->owner_has_unread_messages = 0;
    return $ticket->save();
  }

}