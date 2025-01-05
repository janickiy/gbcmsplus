<?php

namespace mcms\promo\controllers;

use mcms\common\actions\CreateModalAction;
use mcms\common\actions\DisableModelAction;
use mcms\common\actions\EnableModelAction;
use mcms\promo\actions\UpdateAdsTypesModalAction;
use Yii;
use mcms\promo\models\AdsType;
use mcms\promo\models\search\AdsTypeSearch;
use mcms\common\controller\AdminBaseController;

/**
 * AdsTypesController implements the CRUD actions for AdsType model.
 */
class AdsTypesController extends AdminBaseController
{

  /**
   * @inheritdoc
   */
  public $layout = '@app/views/layouts/main';

  /**
   * @inheritdoc
   */
  public function beforeAction($action)
  {
    $this->getView()->title = AdsType::translate('main');
    return parent::beforeAction($action);
  }

  /**
   * @inheritdoc
   */
  public function actions()
  {
    return [
      'update-modal' => [
        'class' => UpdateAdsTypesModalAction::class,
        'modelClass' => AdsType::class
      ],
      'disable' => [
        'class' => DisableModelAction::class,
        'modelClass' => AdsType::class
      ],
      'enable' => [
        'class' => EnableModelAction::class,
        'modelClass' => AdsType::class
      ],
    ];
  }

  /**
   * Lists all AdsType models.
   * @return mixed
   */
  public function actionIndex()
  {
    $searchModel = new AdsTypeSearch(['scenario' => AdsTypeSearch::SCENARIO_ADMIN]);
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider
    ]);
  }

}
