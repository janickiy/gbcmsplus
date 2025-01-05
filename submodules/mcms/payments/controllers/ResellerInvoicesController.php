<?php

namespace mcms\payments\controllers;


use mcms\common\controller\AdminBaseController;
use mcms\common\web\AjaxResponse;
use mcms\payments\components\events\UserBalanceInvoiceCompensation;
use mcms\payments\components\events\UserBalanceInvoiceMulct;
use mcms\payments\models\forms\ResellerConvertForm;
use mcms\payments\models\search\ResellerInvoiceSearch;
use mcms\payments\models\UserBalanceInvoice;
use rgk\utils\actions\IndexAction;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\widgets\ActiveForm;

/**
 *
 * Class ResellerInvoicesController
 * @package mcms\payments\controllers
 */
class ResellerInvoicesController extends AdminBaseController
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
          'convert' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function actions()
  {
    return [
      'index' => [
        'class' => IndexAction::class,
        'modelClass' => ResellerInvoiceSearch::class
      ],
    ];
  }

  /**
   * @param $invoiceId
   * @return \yii\web\Response
   * @throws NotFoundHttpException
   */
  public function actionDownloadFile($invoiceId)
  {
    $model = UserBalanceInvoice::findOne($invoiceId);
    if (!$model) throw new NotFoundHttpException();

    $file = Yii::getAlias($model->file);

    if (!file_exists($file)) {
      throw new NotFoundHttpException('File not found');
    }

    return Yii::$app->response->sendFile($file, null, ['inline' => true]);
  }

  /**
   * Модалка для конвертации валют реселлера
   * @return array|string
   */
  public function actionConvertModal()
  {
    $model = new ResellerConvertForm();
    if ($model->load(Yii::$app->request->post())) {
      if (!Yii::$app->request->post("submit")) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ActiveForm::validate($model);
      }
      return AjaxResponse::set($model->save());
    }

    return $this->renderAjax('_create', [
      'model' => $model
    ]);
  }

  /**
   * Конвертация валют
   * @return array|bool
   */
  public function actionConvert()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $model = new ResellerConvertForm();
    if ($model->load(Yii::$app->request->post())) {
      return [
        'sum' => $model->calcConvert(),
        'balance' => $model->getResellerBalance()
      ];
    }
    return false;
  }
}