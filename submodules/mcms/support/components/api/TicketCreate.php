<?php
namespace mcms\support\components\api;

use mcms\support\models\Support;
use mcms\common\helpers\FormHelper;
use mcms\support\models\SupportText;
use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\support\components\events\EventCreated;
use Yii;

class TicketCreate extends ApiResult
{
  protected $postData;
  protected $userId;
  protected $save;
  protected $formName;

  public function init($params = [])
  {
    $this->postData = ArrayHelper::getValue($params, 'postData');
    $this->filterData($this->postData);
    $this->userId = ArrayHelper::getValue($params, 'userId');
    $this->save = ArrayHelper::getValue($params, 'save');
    $this->formName = ArrayHelper::getValue($params, 'formName');
  }

  public function getResult()
  {
    $support = new Support([
      'scenario' => Support::SCENARIO_CREATE_BY_PARTNER
    ]);
    $support->load($this->postData, $this->formName);
    $support->created_by = Yii::$app->user->id;
    $support->open();

    $supportText = new SupportText([
      'scenario' => SupportText::SCENARIO_CREATE_BY_PARTNER
    ]);
    $supportText->load($this->postData, $this->formName);
    $supportText->from_user_id = Yii::$app->user->id;

    $supportText->detachBehavior('file');

    if (!$this->save) {
      return array_merge(
        FormHelper::validate($support, null, strtolower($this->formName)),
        FormHelper::validate($supportText, null, strtolower($this->formName))
        );
    }

    $success = $support->save();
    $supportText->support_id = $support->id;
    $success &= $supportText->save();
    $support->refresh();
    if ($success) {
      (new EventCreated($support))->trigger();
    }

    return [
      'success' => $success,
      'errors' => array_merge($support->getErrors(), $supportText->getErrors())
    ];
  }
}