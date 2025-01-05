<?php

namespace mcms\payments\controllers;

use mcms\payments\models\Company;
use mcms\payments\models\search\CompanySearch;
use rgk\utils\actions\CreateModalAction;
use rgk\utils\actions\DeleteAjaxAction;
use rgk\utils\actions\IndexAction;
use rgk\utils\actions\UpdateModalAction;
use rgk\utils\components\response\AjaxResponse;
use Yii;
use mcms\payments\components\controllers\BaseController;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * CompaniesController implements the CRUD actions for Company model.
 */
class CompaniesController extends BaseController
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
        ],
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function actions()
  {
    return parent::actions() + [
        'index' => [
          'class' => IndexAction::class,
          'modelClass' => CompanySearch::class,
        ],
        'create' => [
          'class' => CreateModalAction::class,
          'modelClass' => Company::class,
        ],
        'update-modal' => [
          'class' => UpdateModalAction::class,
          'modelClass' => Company::class,
        ],
        'delete' => [
          'class' => DeleteAjaxAction::class,
          'modelClass' => Company::class,
        ],
      ];
  }

  /**
   * Получение лого
   * @param $id
   * @return \yii\console\Response|Response
   * @throws NotFoundHttpException
   */
  public function actionGetLogo($id)
  {
    $model = $this->findModel($id);
    if (!$model->logo) {
      throw new NotFoundHttpException();
    }

    Yii::$app->response->format = Response::FORMAT_HTML;
    $path = $model->getLogoPath();

    return Yii::$app->response->sendFile($path);
  }

  /**
   * @param $id
   * @return string
   * @throws NotFoundHttpException
   */
  public function actionViewModal($id)
  {
    return $this->renderAjax('view-modal', [
      'model' => $this->findModel($id)
    ]);
  }

  /**
   * Finds the Company model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Company the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Company::findOne($id)) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
