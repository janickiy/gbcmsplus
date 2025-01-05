<?php

namespace mcms\payments\controllers;

use mcms\common\controller\AdminBaseController;
use Yii;
use mcms\payments\models\PartnerCompany;
use mcms\payments\models\search\PartnerCompanySearch;
use rgk\utils\actions\CreateModalAction;
use rgk\utils\actions\DeleteAjaxAction;
use rgk\utils\actions\IndexAction;
use rgk\utils\actions\UpdateModalAction;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * PartnerCompaniesController
 */
class PartnerCompaniesController extends AdminBaseController
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

  public function actions()
  {
    return parent::actions() + [
        'index' => [
          'class' => IndexAction::class,
          'modelClass' => PartnerCompanySearch::class,
        ],
        'create' => [
          'class' => CreateModalAction::class,
          'modelClass' => PartnerCompany::class,
        ],
        'update-modal' => [
          'class' => UpdateModalAction::class,
          'modelClass' => PartnerCompany::class,
        ],
        'delete' => [
          'class' => DeleteAjaxAction::class,
          'modelClass' => PartnerCompany::class,
        ],
      ];
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
   * Получение соглашения
   * @param $id
   * @return \yii\console\Response|Response
   * @throws NotFoundHttpException
   */
  public function actionGetAgreement($id)
  {
    Yii::$app->response->format = Response::FORMAT_HTML;
    $model = $this->findModel($id);
    return $model->getAgreementFile();
  }

  /**
   * Finds the PartnerCompany model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return PartnerCompany the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = PartnerCompany::findOne($id)) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
