<?php

namespace mcms\promo\components\widgets;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\search\TrafficBlockSearch;
use mcms\promo\models\search\UserOperatorTrafficFiltersOffSearch;
use Yii;
use yii\base\Widget;

/**
 * Class TrafficFiltersOffWidget
 * @package mcms\promo\components\widgets
 */
class TrafficFiltersOffWidget extends Widget
{
  public $userId;

  private $searchModel;

  /**
   * @inheritDoc
   * @throws \yii\base\InvalidArgumentException
   */
  public function run()
  {
    return self::canView($this->userId)
      ? $this->render('@mcms/promo/components/widgets/views/traffic-filters-off', [
        'dataProvider' => $this->getDataProvider(),
        'userId' => $this->userId,
        'searchModel' => $this->searchModel,
      ])
      : null;
  }

  /**
   * Можно ли показывать виджет
   * @param int $userId
   * @return bool
   */
  public static function canView($userId)
  {
    $canUserHaveTrafficFiltersOff = Yii::$app->authManager->checkAccess($userId, 'CanTrafficFiltersOff');
    $canViewWidget = Yii::$app->user->can('CanViewTrafficFiltersOffWidget');

    return $userId && $canUserHaveTrafficFiltersOff && $canViewWidget;
  }

  /**
   * @return \yii\data\ActiveDataProvider
   */
  protected function getDataProvider()
  {
    $this->searchModel = new UserOperatorTrafficFiltersOffSearch(['user_id' => $this->userId]);
    $dataProvider = $this->searchModel->search(Yii::$app->request->getQueryParams());
    $dataProvider->sort->sortParam = 'tfoSort';
    $dataProvider->pagination->pageParam = 'tfoPage';
    return $dataProvider;
  }
}
