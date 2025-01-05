<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\source\SourceCopy as SourceCopyModel;
use mcms\promo\models\Source as SourceModel;

/**
 * Class SourceCopy
 * @package mcms\promo\components\api
 */
class SourceCopy extends ApiResult
{
  protected $sourceId;
  protected $userId;

  /**
   * @param array $params
   */
  public function init($params = [])
  {
    $this->sourceId = ArrayHelper::getValue($params, 'source_id');

    if (!$this->sourceId) $this->addError('source_id is not set');
  }

  /**
   * @return SourceCopyModel|null
   */
  public function getResult()
  {
    /* @var $source SourceModel */
    if (!$source = SourceModel::findOne($this->sourceId)) {
      $this->addError('Source not found');
      return null;
    }

    $sourceCopier = new SourceCopyModel(['donor' => $source]);

    return $sourceCopier->makeCopy();
  }

}