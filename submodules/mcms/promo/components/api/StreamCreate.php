<?php

namespace mcms\promo\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\promo\models\Stream;

class StreamCreate extends ApiResult
{
  protected $name;
  protected $userId;


  public function init($params = array())
  {
    $this->name = ArrayHelper::getValue($params, 'name', null);
    $this->userId = ArrayHelper::getValue($params, 'userId', null);
  }

  public function getResult()
  {
    return (new Stream([
      'name' => $this->name,
      'user_id' => $this->userId,
      'status' => Stream::STATUS_ACTIVE
    ]))->save();
  }

}
