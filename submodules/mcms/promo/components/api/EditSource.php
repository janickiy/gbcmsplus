<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\models\Source;
use mcms\common\helpers\ArrayHelper;

class EditSource extends ApiResult
{
  protected $userId;
  protected $sourceId;
  protected $adstype;
  protected $default_profit_type;
  protected $filter_operators;
  protected $isOperators;

  public function init($params = [])
  {
    $this->userId = ArrayHelper::getValue($params, 'user_id');
    $this->sourceId = ArrayHelper::getValue($params, 'source_id');
    $this->adstype = ArrayHelper::getValue($params, 'adstype', null);
    $this->default_profit_type = ArrayHelper::getValue($params, 'default_profit_type', null);
    $this->filter_operators = ArrayHelper::getValue($params, 'filter_operators', null);
    $this->isOperators = ArrayHelper::getValue($params, 'isOperators', null);

    if (!$this->userId) $this->addError('user_id is not set');
    if (!$this->sourceId) $this->addError('source_id is not set');
    if (!$this->adstype) $this->addError('adstype is not set');
  }

  public function getResult()
  {
    /* @var $source Source*/
    $source = Source::find()->where([
      'id' => $this->sourceId,
      'user_id' => $this->userId
    ])->one();

    $source->scenario = Source::SCENARIO_PARTNER_UPDATE_WEBMASTER_SOURCE;

    if ($this->adstype !== null) {
      $source->ads_type = $this->adstype;
    }
    if ($this->default_profit_type !== null)  {
      $source->default_profit_type = $this->default_profit_type;
    }
    if ($this->isOperators)  {
      $source->filter_operators = is_array($this->filter_operators) ? $this->filter_operators : [];
    }

    return $source->save();
  }

}