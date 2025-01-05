<?php

namespace mcms\promo\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\promo\models\search\SubscriptionCorrectConditionSearch;
use mcms\promo\models\SubscriptionCorrectCondition;
use rgk\utils\components\response\AjaxResponse;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Условия коррекции подписок
 *
 * Class SubscriptionCorrectConditionsController
 * @package mcms\promo\controllers
 */
class SubscriptionCorrectConditionsController extends AdminBaseController
{
//  public $layout = '@app/views/layouts/main';

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
   * @return string
   */
  public function actionIndex()
  {
    $this->getView()->title = Yii::_t('buyout_conditions.title');
    $searchModel = new SubscriptionCorrectConditionSearch;
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
    ]);
  }

  /**
   * Создание условия
   * @return array|string
   */
  public function actionCreate()
  {
    $model = new SubscriptionCorrectCondition();

    return $this->handleForm($model);
  }

  /**
   * Редактирование условия
   * @param $id
   * @return array|string
   */
  public function actionUpdateModal($id)
  {
    $model = $this->findModel($id);

    return $this->handleForm($model);
  }

  /**
   * @param SubscriptionCorrectCondition $model
   * @return array|string
   */
  protected function handleForm(SubscriptionCorrectCondition $model)
  {
    $request = Yii::$app->request;
    if ($model->load($request->post())) {
      if ($request->post('submit') && $model->save()) {
        return AjaxResponse::success();
      }

      Yii::$app->response->format = Response::FORMAT_JSON;

      return ActiveForm::validate($model);
    }

    return $this->renderAjax('form', [
      'model' => $model,
    ]);
  }

  /**
   * @param $id
   * @return array
   */
  public function actionDelete($id)
  {
    return AjaxResponse::set($this->findModel($id)->delete());
  }

  /**
   * @param int $id
   * @return SubscriptionCorrectCondition
   * @throws NotFoundHttpException
   */
  protected function findModel($id)
  {
    $model = SubscriptionCorrectCondition::findOne($id);
    if (!$model) {
      throw new NotFoundHttpException('The requested page does not exist.');
    }

    return $model;
  }
}
