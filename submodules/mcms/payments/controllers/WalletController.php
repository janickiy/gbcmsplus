<?php

namespace mcms\payments\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\common\web\AjaxResponse;
use mcms\payments\components\WalletDisable;
use mcms\payments\components\WalletEnable;
use mcms\payments\Module;
use Yii;
use mcms\payments\models\wallet\Wallet;
use mcms\payments\models\wallet\WalletSearch;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * WalletController implements the CRUD actions for Wallet model.
 */
class WalletController extends AdminBaseController
{

  /**
   * @return array
   */
  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'update-modal' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * Lists all Wallet models.
   * @return mixed
   */
  public function actionIndex()
  {
    $this->getView()->title = Yii::_t('payments.menu.wallet-list');
    $this->setBreadcrumb('payments.menu.wallet-list');

    $searchModel = new WalletSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
    ]);
  }

  /**
   * Редактирование в модалке
   * @param $id
   * @return mixed
   */
  public function actionUpdateModal($id)
  {
    $model = $this->findModel($id);

    if ($model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post("submit")) {
        return AjaxResponse::set($model->save());
      }
      return ActiveForm::validate($model);
    }
    /** @var Module $module */
    $module = $this->module;
    return $this->renderAjax('form', [
      'model' => $model,
      'canEditAllFields' => $module->isUserCanEditAllWalletFields()
    ]);
  }

  /**
   * Finds the Wallet model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Wallet the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Wallet::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * @param $id
   * @return array
   */
  public function actionEnable($id)
  {
    return AjaxResponse::set((new WalletEnable($id))->execute());
  }

  /**
   * @param $id
   * @return array
   */
  public function actionDisable($id)
  {
    return AjaxResponse::set((new WalletDisable($id))->execute());
  }
}
