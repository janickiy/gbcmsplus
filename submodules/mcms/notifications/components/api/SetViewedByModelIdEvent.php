<?php
namespace mcms\notifications\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\StringEncoderDecoder;
use mcms\common\module\api\ApiResult;
use mcms\notifications\models\BrowserNotification;
use mcms\notifications\Module;
use Yii;

/**
 * setViewedByIdEvent
 * Class SetViewedByModelIdEvent
 * @package mcms\notifications\components\api
 */
class SetViewedByModelIdEvent extends ApiResult
{
  protected $notificationModelId;
  protected $event;
  protected $userId;
  private $isModelId = false;

  public function init($params = [])
  {
    $this->event = ArrayHelper::getValue($params, 'event');

    if (ArrayHelper::getValue($params, 'onlyOwner')) {
      $this->userId = Yii::$app->user->id;
    }

    if ($modelId = ArrayHelper::getValue($params, 'modelId')) {
      $this->notificationModelId = $modelId;
      $this->isModelId = true;
      return ;
    }

    $decodedNotificationId = ArrayHelper::getValue($params, Module::FN_QUERY_PARAM);
    if (!$decodedNotificationId) return ;

    if ($id = (int)$decodedNotificationId) {
      $this->notificationModelId = $id;

      // tricky: Если не передан event, получаем его по id уведомления
      $this->event = $this->event
        ?: BrowserNotification::find()->andWhere(['id' => $this->notificationModelId])->select('event')->scalar();

      return ;
    }
    $this->notificationModelId = (int)StringEncoderDecoder::decode($decodedNotificationId);
    $this->isModelId = true;
  }

  public function getResult()
  {
    if (!$this->notificationModelId || !$this->event) return ;

    $this->isModelId
      ? BrowserNotification::setViewedByModelId($this->notificationModelId, $this->event, $this->userId)
      : BrowserNotification::setViewedById($this->notificationModelId, $this->event, $this->userId)
      ;
  }

}