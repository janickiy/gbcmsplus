<?php


namespace mcms\common\web;

use Yii;
use yii\base\Object;

/**
 * Class Response
 * @package mcms\common\web
 */
class Response extends Object implements \JsonSerializable
{

  const DEFAULT_SUCCESS_PARAM = 'success';
  const DEFAULT_ERROR_PARAM = 'error';
  const DEFAULT_DATA_PARAM = 'data';

  /**
   * @var bool
   */
  public $success;
  /**
   * @var string
   */
  public $error;
  /**
   * @var string
   */
  public $data;

  /**
   * @param string $successParam
   * @param string $errorParam
   * @param string $dataParam
   * @return array
   */
  public function asArray($successParam = self::DEFAULT_SUCCESS_PARAM, $errorParam = self::DEFAULT_ERROR_PARAM, $dataParam = self::DEFAULT_DATA_PARAM)
  {
    return [
      $successParam => $this->success,
      $errorParam => $this->error,
      $dataParam => $this->data,
    ];
  }

  /**
   * @inheritdoc
   */
  public function jsonSerialize()
  {
    return $this->asArray();
  }

  /**
   * @param integer $code Код для статуса ответа
   */
  public static function setStatusCode($code)
  {
    Yii::$app->response->statusCode = $code;
  }

  /**
   * Установка кода статуса ответа 500 Internal Server Error
   */
  public static function setStatusISError()
  {
    self::setStatusCode(500);
  }

  /**
   * Установка кода статуса ответа 200 Ok
   */
  public static function setStatusOk()
  {
    self::setStatusCode(200);
  }
}