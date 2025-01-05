<?php

namespace mcms\statistic\controllers;

use mcms\common\controller\AdminBaseController;
use Yii;
use mcms\statistic\models\search\PostbackSearch;

/**
 * PostbackController implements the CRUD actions for Postback model.
 */
class PostbackController extends AdminBaseController
{
  /**
   * Lists all Postback models.
   * @return mixed
   */
  public function actionIndex()
  {
    $this->setBreadcrumb('main.postbacks');
    $this->getView()->title = Yii::_t('main.postbacks');

    $searchModel = new PostbackSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';

    if (!empty($_POST['exportFull_' . $exportWidgetId])) {
      $dataProvider->setPagination(['pageSize' => Yii::$app->getModule('statistic')->getExportPostbackLimit()]);
    }

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
      'exportWidgetId' => $exportWidgetId,
    ]);
  }
}
