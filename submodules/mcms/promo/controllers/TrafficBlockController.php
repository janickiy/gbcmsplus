<?php

namespace mcms\promo\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\promo\components\api\UserPromoSettings;
use rgk\utils\components\response\AjaxResponse;
use Yii;
use mcms\promo\models\TrafficBlock;
use mcms\promo\models\search\TrafficBlockSearch;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * TrafficBlockController implements the CRUD actions for TrafficBlock model.
 */
class TrafficBlockController extends AdminBaseController
{
  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'delete' => ['POST'],
          'update-modal' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * Lists all TrafficBlock models.
   * @return mixed
   */
  public function actionList()
  {
    $this->getView()->title = Yii::_t('promo.traffic_block.menu');
    $searchModel = new TrafficBlockSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('list', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
    ]);
  }

  /**
   * Удаление блокировки
   * @param int $id
   * @return array
   */
  public function actionDelete($id)
  {
    return AjaxResponse::set($this->findModel($id)->delete());
  }

  /**
   * Updates an existing TrafficBlock model.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdateModal($id, $userId = null)
  {
    return $this->handleAjaxForm($this->findModel($id), !$userId);
  }

  /**
   * Creates a new TrafficBlock model.
   * @return mixed
   */
  public function actionCreateModal($userId = null)
  {
    $model = new TrafficBlock;
    if ($userId) {
      $model->user_id = $userId;
    }
    return $this->handleAjaxForm($model, !$userId);
  }

  /**
   * @param TrafficBlock $model
   * @param bool $showUser
   * @return array|string
   */
  private function handleAjaxForm($model, $showUser)
  {
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post('submit')) {
        return AjaxResponse::set($model->save());
      }
      return ActiveForm::validate($model);
    }
    return $this->renderAjax('form-modal', [
      'model' => $model,
      'showUser' => $showUser,
    ]);
  }

  /**
   * Виджет аякс-переключателя режима блокировки операторов для партнера
   * @param $userId
   * @return array
   */
  public function actionSwitchPartner($userId)
  {
    $value = Yii::$app->request->post('value');
    return AjaxResponse::set((new UserPromoSettings())->saveIsBlacklistTrafficBlocks($userId, $value));
  }

  /**
   * Finds the TrafficBlock model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return TrafficBlock the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = TrafficBlock::findOne($id)) !== null) {
      return $model;
    }
    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
