<?php
namespace mcms\notifications\components\api;

use Yii;
use yii\caching\TagDependency;
use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\notifications\models\BrowserNotification;


class BrowserNotificationSetHidden extends ApiResult
{
  protected $userId;
  protected $modelId;
  protected $event;

  public function init($params = [])
  {
    $this->userId = ArrayHelper::getValue($params, 'user_id');
    $this->modelId = ArrayHelper::getValue($params, 'model_id');
    $this->event = ArrayHelper::getValue($params, 'event');
  }

  public function getResult()
  {
    return BrowserNotification::setViewed($this->userId, $this->modelId, $this->event, true);
  }

}