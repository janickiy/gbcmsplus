<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\models\Source;
use mcms\common\helpers\ArrayHelper;

class GetSource extends ApiResult
{
  protected $userId;
  protected $sourceId;

  public function init($params = [])
  {
    $this->userId = ArrayHelper::getValue($params, 'user_id');
    $this->sourceId = ArrayHelper::getValue($params, 'source_id');
    if (!$this->userId) $this->addError('user_id is not set');
    if (!$this->sourceId) $this->addError('source_id is not set');
  }

  public function getResult()
  {
    return Source::find()->where([
        Source::tableName() . '.id' => $this->sourceId,
        'user_id' => $this->userId,
      ])->joinWith('sourceOperatorLanding')->joinWith('sourceOperatorLanding.landing')->one();
  }

}