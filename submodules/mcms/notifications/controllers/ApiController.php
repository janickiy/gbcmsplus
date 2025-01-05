<?php

namespace mcms\notifications\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\notifications\components\telegram\Api;
use Yii;
use yii\rest\Controller;

/**
 * Class ApiController
 * @package mcms\notifications\controllers
 */
class ApiController extends Controller
{
  /** @var Api */
  private $api;

  public function init()
  {
    parent::init();
    $this->api = new Api();
  }

  /**
   * Слушаем ответ Telegram на запрос пользователя о подписке на канал
   * @return mixed
   */
  public function actionTelegram()
  {
    $message = Yii::_t('notifications.telegram_api.hello');
    if ($this->api->getUserId()) {

      $userParams = Yii::$app->getModule('users')
        ->api('userParams', ['userId' => $this->api->getUserId()])
        ->getResult();
      $telegramId = ArrayHelper::getValue($userParams, 'telegram_id');

      if ($telegramId) {
        return $this->api->sendMessage(
          Yii::_t('notifications.telegram_api.already_subscribed')
        );
      }

      $result = Yii::$app->getModule('users')
        ->api('userTelegram', ['userId' => $this->api->getUserId()])
        ->setTelegramId($this->api->getChatId());


      $message = Yii::_t('notifications.telegram_api.successfully_subscribed');
      if (!$result) {
        $message = Yii::_t('notifications.telegram_api.error');
      }
    }

    return $this->api->sendMessage(
      $message
    );
  }
}