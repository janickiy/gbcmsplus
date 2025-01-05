<?php

namespace mcms\notifications\components\push;


use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\base\Component;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Response;

/**
 * @see https://habrahabr.ru/post/321924/
 * Class Api
 * @package mcms\notifications\components\push
 */
class Api extends Component
{
  // Firebase Cloud Messaging
  const FCM_URL = 'https://fcm.googleapis.com/fcm/send';
  /** @var  string Ключ сервера*/
  private $apiKey;
  /** @var  string адрес иконки для уведомлений */
  public $icon;

  private $_lastResponce;

  public function init()
  {
    parent::init();
    $this->apiKey = Yii::$app->getModule('notifications')->pushApiKey;
    if (!$this->icon) {
      $this->icon = Url::to(Yii::$app->getModule('notifications')->getPushIconUrl(), true);
    }

  }

  /**
   * Отправить сообщение
   * @param $tokenId
   * @param $title
   * @param $body
   * @param null|string $link
   * @param string $method
   * @return bool
   */
  public function send($tokenId, $title, $body, $link = null, $method = 'post')
  {

    $client = new Client([
      'transport' => CurlTransport::class,
    ]);
    $data = $this->getRequestBody($tokenId, $title, $body, $link);
    $response = $client->createRequest()
      ->addHeaders([
        'Authorization' =>  'key=' . $this->apiKey,
      ])
      ->setFormat(Client::FORMAT_JSON)
      ->setMethod($method)
      ->setUrl(self::FCM_URL)
      ->setData($data)
      ->setOptions([
        'followLocation' => true,
      ])
      ->send();

    $this->setResponse($response);
    return self::isOk($response);
  }

  /**
   * @param Response $response
   */
  private function setResponse(Response $response)
  {
    $this->_lastResponce = $response;
  }

  /**
   * @return null|Response
   */
  public function getResponse()
  {
    return $this->_lastResponce;
  }

  /**
   * Формирование тела запроса
   * @param $tokenId
   * @param $title
   * @param $body
   * @param null $link
   * @return string
   */
  private function getRequestBody($tokenId, $title, $body, $link = null)
  {
    return [
      'to' => $tokenId,
      'notification' => [
        'title' => $title,
        'body' => $body,
        'icon' => $this->icon,
        'click_action' => $link,
      ],
    ];
  }

  /**
   * Проверяем, удачно ли отправлено сообщение
   * @param $response Response
   * @return bool
   */
  private static function isOk($response)
  {
    $arrayResult = Json::decode($response->getContent());
    return (bool)ArrayHelper::getValue($arrayResult, 'success');

  }
}