<?php
namespace mcms\support\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\FormHelper;
use mcms\common\module\api\ApiResult;
use mcms\support\models\Support;
use mcms\support\models\SupportText;
use Yii;
use yii\web\ForbiddenHttpException;

class TicketSendMessage extends ApiResult
{
  protected $ticketId;
  protected $postData;
  protected $save;
  protected $formName;

  public function init($params = [])
  {
    $this->ticketId = ArrayHelper::getValue($params, 'ticketId', null);
    $this->postData = ArrayHelper::getValue($params, 'postData', null);
    $this->filterData($this->postData);
    $this->save = ArrayHelper::getValue($params, 'save');
    $this->formName = ArrayHelper::getValue($params, 'formName');

    if (!$this->ticketId ) $this->addError('ticketId is not set');
  }

  public function getResult()
  {
    /** @var Support $support */
    $support = Support::findOne(['id' => $this->ticketId]);
    if ($support === null || !$support->is_opened) {
      return false;
    }
    if(!$support->canManageOwnTicket()) throw new ForbiddenHttpException('Access denied');

    $supportText = new SupportText([
      'scenario' => SupportText::SCENARIO_CREATE_BY_PARTNER,
      'support_id' => $this->ticketId
    ]);

    $supportText->load($this->postData, $this->formName);
    $supportText->from_user_id = Yii::$app->user->id;

    $supportText->detachBehavior('file');

    if (!$this->save) {
      return FormHelper::validate($supportText, null, strtolower($this->formName));
    }

    return [
      'success' => $supportText->save(),
      'errors' => $supportText->getErrors()
    ];
  }

}