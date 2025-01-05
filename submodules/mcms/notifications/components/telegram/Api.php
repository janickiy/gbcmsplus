<?php

namespace mcms\notifications\components\telegram;


use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Response;

/**
 * Class Api
 * @package mcms\notifications\components\telegram
 */
class Api extends Component
{
  const SOLT = 'solt';

  private static $_botToken;
  private static $_botName;
  private static $_responseUrl;
  private $_chatId;
  private $_text;
  private $_userId;

  /**
   * Возвращает URL для доступа к АПИ telegram
   * @return string
   */
  private static function getResponseUrl()
  {
    if (self::$_responseUrl) return self::$_responseUrl;
    return self::$_responseUrl = 'https://api.telegram.org/bot' . self::getToken();
  }

  /**
   * Получить токен для Telegram бота
   * @return string
   */
  private static function getToken()
  {
    if (self::$_botToken) return self::$_botToken;
    return self::$_botToken = Yii::$app->getModule('notifications')->getTelegramBotToken();
  }

  /**
   * Получить имя Telegram бота
   * @return string
   */
  private static function getBotName()
  {
    if (self::$_botName) return self::$_botName;
    return self::$_botName = Yii::$app->getModule('notifications')->getTelegramBotName();
  }

  /**
   * Отправляем сформированное сообщение обратно в Telegram пользователю
   * @param $message string Текст сообщения
   * @param $chatId int чат, в который необходимо отправить сообщение
   * @return bool
   */
  public function sendMessage($message, $chatId = null)
  {
    // если чат не задан, считаем, что нужно ответить на запрос пользователя
    // и пытаемся парсить запрос от Telegram в поисках chatId
    $chatId = $chatId ?: $this->getChatId();

    if (!$chatId) {
      return false;
    }

    $response = self::send(
      self::getResponseUrl() . '/sendMessage',
      ['chat_id' => $chatId, 'text' => $message, 'parse_mode' => 'HTML']
    );
    return self::isOk($response);
  }

  /**
   * Проверяем, удачно ли отправлено сообщение
   * @param $response Response
   * @return bool
   */
  private static function isOk($response)
  {
    try {
      $arrayResult = Json::decode($response->getContent());
    } catch (InvalidArgumentException $e) {
      Yii::error('Parse JSON error. JSON data: ' . (string)$response->getContent(), __METHOD__);
      return false;
    }
    return (bool)ArrayHelper::getValue($arrayResult, 'ok');

  }

  /**
   * Отправить запрос
   * @param $url
   * @param $data
   * @return Response
   */
  private static function send($url, $data = [], $method = 'post')
  {
    $client = new Client([
      'transport' => CurlTransport::class,
    ]);
    return $client->createRequest()
      ->setMethod($method)
      ->setUrl($url)
      ->setData($data)
      ->send();
  }

  /**
   * Парсим запрос от Telegram
   * @return bool
   */
  private function parseRequest()
  {
    $content = file_get_contents('php://input');
    $update = Json::decode($content);
    $message = ArrayHelper::getValue($update, 'message', []);

    // Получаем текст, введённый пользователем в чате
    $this->_text = ArrayHelper::getValue($message, 'text');
    if (!$this->_text) {
      return false;
    }

    // Получаем внутренний номер чата Telegram
    $this->_chatId = ArrayHelper::getValue(
      ArrayHelper::getValue($message, 'chat', []),
      'id'
    );
    if (!$this->_chatId) {
      return false;
    }

    return true;
  }

  /**
   * внутренний номер чата Telegram
   * @return int|null
   */
  public function getChatId()
  {
    if (!$this->_chatId) $this->parseRequest();
    return $this->_chatId;
  }

  /**
   * Текст, введенный пользователем
   * @return string|null
   */
  public function getText()
  {
    if (!$this->_text) $this->parseRequest();
    return $this->_text;
  }

  /**
   * Получить id пользователя (в нашей системе)
   * @return int|null
   */
  public function getUserId()
  {
    if ($this->_userId) return $this->_userId;

    if (!$this->getText()) {
      return null;
    }

    $textString = explode(' ', $this->getText());
    $command = ArrayHelper::getValue($textString, 0);
    $userData = ArrayHelper::getValue($textString, 1);

    // если это не команда /start или пользователь ничего не передал, возвращаем NULL
    if ($command != '/start' || !$userData) {
      return null;
    }

    $decodedUserData = explode('_', self::hexToStr($userData));
    $this->_userId = ArrayHelper::getValue($decodedUserData, 0);
    $solt = ArrayHelper::getValue($decodedUserData, 1);

    // если передана неправильная соль, возвращаем NULL
    if ($solt != self::getSolt($this->_userId)) {
      return null;
    }

    return $this->_userId;
  }

  /**
   * Возвращает адрес для подписки на телеграм рассылку
   * @return string
   */
  public static function getStartUrl()
  {
    $data = self::strToHex(Yii::$app->user->id . '_' . self::getSolt(Yii::$app->user->id));
    return 'https://telegram.me/' . self::getBotName() . '?start=' . $data;
  }

  /**
   * Солим/перчим
   * @param $userId
   * @return string
   */
  private static function getSolt($userId)
  {
    return crypt($userId, self::SOLT);
  }

  /**
   * Установить хук
   * @param $url string|null Если поставить $url = null, хук будет убран
   * @return mixed
   */
  public static function setWebhook($url = null)
  {
    return self::send(
      self::getResponseUrl() . '/setWebhook',
      ['url' => $url]
    );
  }

  /**
   * Зашифровываем строку
   * @param $string
   * @return string
   */
  private static function strToHex($string)
  {
    $hex = '';
    for ($i = 0; $i < strlen($string); $i++) {
      $ord = ord($string[$i]);
      $hexCode = dechex($ord);
      $hex .= substr('0' . $hexCode, -2);
    }
    return strtoupper($hex);
  }

  /**
   * Расшифровываем строку
   * @param $hex
   * @return string
   */
  private static function hexToStr($hex)
  {
    $string = '';
    for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
      $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
    }
    return $string;
  }
}