<?php

namespace mcms\statistic\controllers;

use mcms\common\controller\AdminBaseController;
use rgk\utils\actions\IndexAction;
use Yii;
use mcms\statistic\models\PostbackData;
use mcms\statistic\models\search\PostbackDataSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PostbackDataController implements the CRUD actions for PostbackData model.
 */
class PostbackDataController extends AdminBaseController
{

  /**
   * Lists all PostbackData models.
   * @return mixed
   */
  public function actionIndex()
  {
    $searchModel = new PostbackDataSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
    ]);
  }

  /**
   * Displays a single PostbackData model.
   * @param string $id
   * @return mixed
   */
  public function actionViewModal($id)
  {
    return $this->renderAjax('view-modal', [
      'model' => $this->findModel($id),
    ]);
  }


  /**
   * Finds the PostbackData model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param string $id
   * @return PostbackData the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = PostbackData::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
  }
}
