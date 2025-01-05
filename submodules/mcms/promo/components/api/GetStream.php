<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\models\Stream;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * api stream
 * Class GetStream
 * @package mcms\promo\components\api
 */
class GetStream extends ApiResult
{
  private $streamId;

  function init($params = [])
  {
    $this->streamId = ArrayHelper::getValue($params, 'streamId');
  }

  public function getResult()
  {
    return Stream::findOne($this->streamId);
  }

  /**
   * Возвращает массив для построения ссылки
   * на просмотр отфильтрованного по id потока в гриде потоков
   */
  public function getGridViewUrlParam()
  {
    return ['/promo/streams/index/', 'StreamSearch[id]' => $this->streamId];
  }

}