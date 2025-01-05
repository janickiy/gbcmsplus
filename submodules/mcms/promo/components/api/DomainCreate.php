<?php

namespace mcms\promo\components\api;

use Yii;
use mcms\common\helpers\FormHelper;
use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Domain;

class DomainCreate extends ApiResult
{
  protected $postData;
  protected $userId;
  protected $save;
  protected $formName;

  public function init($params = [])
  {
    $this->postData = ArrayHelper::getValue($params, 'postData');
    $this->userId = ArrayHelper::getValue($params, 'userId');
    $this->save = ArrayHelper::getValue($params, 'save');
    $this->formName = ArrayHelper::getValue($params, 'formName');
    $this->type = Domain::TYPE_PARKED; // TRICKY: Все домены добавляются как припаркованные
  }

  public function getResult()
  {
    $domain = new Domain([
      'status' => Domain::STATUS_ACTIVE,
      'type' => $this->type,
      'is_system' => 0,
      'user_id' => $this->userId,
      'created_by' => $this->userId,
    ]);
    $domain->scenario = $this->type == Domain::TYPE_NORMAL ? Domain::SCENARIO_PARTNER_REGISTER : Domain::SCENARIO_PARTNER_PARK;

    $domain->load($this->postData, $this->formName);

    if (!$this->save) {
      return FormHelper::validate($domain, null, strtolower($this->formName));
    } else {
      return [
        'success' => $domain->save(),
        'id' => $domain->id,
        'url' => $domain->url,
        'errors' => $domain->getErrors()
      ];
    }
  }
}