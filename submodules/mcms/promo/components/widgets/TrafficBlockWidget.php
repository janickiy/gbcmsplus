<?php

namespace mcms\promo\components\widgets;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\search\TrafficBlockSearch;
use Yii;
use yii\base\Widget;

/**
 * Class TrafficBlockWidget
 * @package mcms\promo\components\widgets
 */
class TrafficBlockWidget extends Widget
{

  private $userId;
  private $showAddButton;

  public $options;

  private $queryParams = [];

  private $searchModel;

  public function init()
  {
    $this->userId = ArrayHelper::getValue($this->options, 'userId');
    $this->queryParams = Yii::$app->request->getQueryParams();
    $this->showAddButton = ArrayHelper::getValue($this->options, 'showAddButton', false);
    parent::init();
  }
  /**
   * @inheritDoc
   */
  public function run()
  {
    // Если пользователь не может иметь блокировки трафика, выходим
    if ($this->userId && !Yii::$app->authManager->checkAccess($this->userId, 'CanHaveTrafficBlock')) return;

    // Если нет прав на просмотр виджета, выходим
    return Yii::$app->user->can('CanViewTrafficBlockWidget')
      ? $this->render('@mcms/promo/views/traffic-block/traffic_block', [
        'dataProvider' => $this->getDataProvider(),
        'userId' => $this->userId,
        'searchModel' => $this->searchModel,
        'showAddButton' => $this->showAddButton,
      ])
      : null;
  }

  /**
   * @return \yii\data\ActiveDataProvider
   */
  protected function getDataProvider()
  {
    $this->searchModel = new TrafficBlockSearch(['user_id' => $this->userId]);
    $dataProvider = $this->searchModel->search($this->queryParams);
    $dataProvider->sort->sortParam = 'trafBlockSort';
    $dataProvider->pagination->pageParam = 'trafBlockPage';
    return $dataProvider;
  }
}
