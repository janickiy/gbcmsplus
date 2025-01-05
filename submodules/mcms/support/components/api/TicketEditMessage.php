<?php
namespace mcms\support\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\support\models\SupportText;
use yii\web\ForbiddenHttpException;

class TicketEditMessage extends ApiResult
{
  protected $messageId;
  protected $postData;

  public function init($params = [])
  {
    $this->messageId = ArrayHelper::getValue($params, 'message_id');
    $this->postData = ArrayHelper::getValue($params, 'post_data');
    $this->filterData($this->postData);

    if (!$this->messageId ) $this->addError('message_id is not set');
  }

  public function getResult()
  {
    /** @var SupportText $supportText */
    $supportText = SupportText::findOne(['id' => $this->messageId]);
    if ($supportText === null) {
      return false;
    }

    if(!$supportText->canManageOwnTicketText()) throw new ForbiddenHttpException('Access denied');

    $supportText->text =  ArrayHelper::getValue($this->postData, 'text');
    $supportText->images = ArrayHelper::getValue($this->postData, 'images');

    return $supportText->save();
  }

}