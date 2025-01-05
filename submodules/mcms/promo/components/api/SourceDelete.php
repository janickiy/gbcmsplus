<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Source;
use Yii;

class SourceDelete extends ApiResult
{
  protected $sourceId;
  protected $userId;

  public function init($params = [])
  {
    $this->sourceId = ArrayHelper::getValue($params, 'source_id');
    $this->userId = ArrayHelper::getValue($params, 'user_id');

    if (!$this->userId) $this->addError('user_id is not set');
    if (!$this->sourceId) $this->addError('source_id is not set');
  }

  public function getResult()
  {
    /* @var $source Source*/
    $source = Source::findOne(['id' => $this->sourceId, 'user_id' => $this->userId]);

    if(!$source) {
      $this->addError('Source not found');
      return false;
    }
    $source->status = Source::STATUS_INACTIVE;
    $source->deleted_by = Yii::$app->user->id;

    return $source->save(true, ['status', 'deleted_by']);
  }

}