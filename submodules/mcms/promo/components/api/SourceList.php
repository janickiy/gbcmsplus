<?php

namespace mcms\promo\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\promo\models\search\SourceSearch;
use mcms\promo\models\SmartLink;
use Yii;
use yii\db\ActiveRecord;

class SourceList extends ApiResult
{
  private $_searchModel;
  /** @var bool добавлять к выдаче смарт ссылки */
  private $addSmartLink = false;

  public function init($params = [])
  {
    $this->_searchModel = new SourceSearch();

    $statFilters = ArrayHelper::getValue($params, 'statFilters');
    $this->addSmartLink = ArrayHelper::getValue($params, 'addSmartLink', false);

    if ($statFilters) {
      $this->_searchModel->scenario = SourceSearch::SCENARIO_STAT_FILTERS;
    }

    if (in_array('orderByStreamName', $params, true)) {
      $this->_searchModel->orderByStreamName = true;
    }

    $this->setResultTypeDataProvider();
    $this->prepareDataProvider($this->_searchModel, $params);

    if ($statFilters) {
      // убираем пагинацию, чтобы не было лишнего запроса COUNT(*)
      $this->dataProvider->setPagination(false);
      Yii::$app->getModule('statistic')->api('statFilters')->filterSources($this->dataProvider->query, ArrayHelper::getValue($params, 'statFiltersUser'));
    }
  }

  public function getTypeWebmasterSource()
  {
    return SourceSearch::SOURCE_TYPE_WEBMASTER_SITE;
  }

  public function getTypeArbitraryLink()
  {
    return SourceSearch::SOURCE_TYPE_LINK;
  }

  /**
   * @return SourceSearch
   */
  public function getSearchModel()
  {
    return $this->_searchModel;
  }

  /**
   * Добавление смарт ссылок в выдачу, если необходимо
   * @param ActiveRecord $searchModel
   * @param array $params
   */
  public function prepareDataProvider(ActiveRecord $searchModel, $params = [])
  {
    parent::prepareDataProvider($searchModel, $params);
    if (!$this->addSmartLink) {
      return;
    }

    $userId = ArrayHelper::getValue($params, 'conditions.user_id');
    $smartLinks = $this->getSearchModel()->getSmartLinkOperatorsCount() ? $this->getSmartLinks($userId) : [];
    $models = array_merge($smartLinks, $this->dataProvider->getModels());

    $this->dataProvider->setModels($models);
    $this->dataProvider->setKeys(null);
  }

  /**
   * Получить массив объектов смарт-ссылок
   * @param integer $userId
   * @return array
   */
  private function getSmartLinks($userId = null)
  {
    if (!$userId) {
      return [];
    }
    $smartLink = SmartLink::createForUser($userId, false);

    return $smartLink ? [$smartLink] : [];
  }
}