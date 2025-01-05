<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\models\Source as SourceModel;
use mcms\common\helpers\ArrayHelper;

/**
 * api sourceById
 * Class GetSourceById
 * @package mcms\promo\components\api
 */
class GetSourceById extends ApiResult
{
  protected $sourceId;

  public function init($params = [])
  {
    $this->sourceId = ArrayHelper::getValue($params, 'source_id');

    if (!$this->sourceId) $this->addError('source_id is not set');
  }

  /**
   * @return \mcms\promo\models\Source
   */
  public function getResult()
  {
    return SourceModel::findOne($this->sourceId);
  }

  public function getUrlParam()
  {
    $source = $this->getResult();
    if ($source->source_type == SourceModel::SOURCE_TYPE_LINK) {
      return ['/promo/arbitrary-sources/view', 'id' => $this->sourceId];
    }

    if ($source->source_type == SourceModel::SOURCE_TYPE_WEBMASTER_SITE) {
      return ['/promo/webmaster-sources/view', 'id' => $this->sourceId];
    }

    if ($source->source_type == SourceModel::SOURCE_TYPE_SMART_LINK) {
      return ['/promo/smart-links/view', 'id' => $this->sourceId];
    }
  }

}