<?php


namespace mcms\common\web;


use mcms\common\helpers\Html;
use yii\web\Response as YiiResponse;
use Yii;

class AjaxResponse extends Response
{
  /**
   * Успех
   * @param mixed $data
   * @param string $dataParam
   * @return array
   */
  static public function success($data = null, $dataParam = self::DEFAULT_DATA_PARAM)
  {
    Yii::$app->response->format = YiiResponse::FORMAT_JSON;
    return (new self([
      'success' => true,
      'data' => $data
    ]))->asArray(self::DEFAULT_SUCCESS_PARAM, self::DEFAULT_ERROR_PARAM, $dataParam);
  }

  /**
   * Ошибка
   * TRICKY Если JS после использования метода отображениет сообщение об успехе, значит ему нужен соответствующий HTTP-код.
   * Например:
   * ```
   * Yii::$app->response->statusCode = 500;
   * ```
   * @param string|array $error
   * @return array
   */
  static public function error($error = '')
  {
    Yii::$app->response->format = YiiResponse::FORMAT_JSON;
    return (new self([
      'success' => false,
      'error' => $error,
    ]))->asArray();
  }

  /**
   * Автоопределение результата (успех/ошибка)
   * @param bool $result
   * @param mixed $data @see success()
   * @param string $dataParam @see success()
   * @return array
   */
  static public function set($result, $data = null, $dataParam = self::DEFAULT_DATA_PARAM)
  {
    return (bool) $result ? self::success($data, $dataParam) : self::error();
  }
}
