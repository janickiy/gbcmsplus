<?php

namespace mcms\statistic\components\api;

use mcms\statistic\models\search\PostbackSearch;
use Yii;
use mcms\common\module\api\ApiResult;

/**
 * Список постбеков
 * Class PostbacksList
 * @package mcms\statistic\components\api
 */
class PostbacksList extends ApiResult
{
  /**
   * @inheritdoc
   */
  public function init($params = [])
  {
    $this->setResultTypeDataProvider();

    $this->prepareDataProvider(new PostbackSearch(), $params);
  }

  public function getCount()
  {
    return $this->dataProvider->getTotalCount();
  }
}
