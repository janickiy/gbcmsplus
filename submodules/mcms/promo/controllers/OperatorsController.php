<?php

namespace mcms\promo\controllers;

use mcms\common\helpers\Select2;
use mcms\common\web\AjaxResponse;
use Yii;
use mcms\promo\models\Country;
use mcms\promo\models\Operator;
use mcms\promo\models\search\OperatorSearch;
use mcms\common\controller\AdminBaseController;
use yii\base\Exception;
use yii\bootstrap\ActiveForm;
use yii\helpers\BaseHtml;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\ArrayHelper;

/**
 * OperatorsController implements the CRUD actions for Operator model.
 */
class OperatorsController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';

  public function beforeAction($action)
  {
    $this->getView()->title = Yii::_t('promo.operators.main');

    return parent::beforeAction($action);
  }

  public function actions()
  {
    return [
      'update-params' => [
        'class' => \mcms\common\actions\EditableAction::class,
        'callback' => 'updateParams',
      ],
    ];
  }

  /**
   * Lists all Operator models.
   * @return mixed
   */
  public function actionIndex()
  {
    $searchParams = ['scenario' => OperatorSearch::SCENARIO_ADMIN];
    if (!Yii::$app->user->can('PromoOperatorsDetailList')) $searchParams['status'] = Operator::STATUS_ACTIVE;
    $searchModel = new OperatorSearch($searchParams);
    $searchModel->load(Yii::$app->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $searchModel->search(Yii::$app->request->queryParams),
      'countries' => Country::getDropdownItems(Yii::$app->user->can('PromoOperatorsDetailList') ? null : Country::STATUS_ACTIVE),
      'canChangeOperatorShowServiceUrl' => Yii::$app->getModule('promo')->canChangeOperatorShowServiceUrl(),
    ]);
  }

  /**
   * Displays a single Operator model.
   * @param integer $id
   * @return string
   */
  public function actionView($id)
  {
    $model = $this->findModel($id);
    $this->getView()->title = $model->name;

    return $this->render('view', [
      'model' => $model,
      'statisticModule' => Yii::$app->getModule('statistic')
    ]);
  }

  /**
   * Displays a single Operator model.
   * @param integer $id
   * @return string
   */
  public function actionViewModal($id)
  {
    $model = $this->findModel($id);
    $this->getView()->title = $model->name;

    return $this->renderAjax('view_modal', [
      'model' => $model,
      'statisticModule' => Yii::$app->getModule('statistic')
    ]);
  }

  /**
   * Creates a new Operator model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   * @throws Exception
   * @throws \Exception
   * @throws \yii\db\Exception
   */
  public function actionCreate()
  {
    return $this->handleAjaxForm(new Operator());
  }

  /**
   * Updates an existing Operator model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   * @throws Exception
   * @throws NotFoundHttpException
   * @throws \Exception
   * @throws \yii\db\Exception
   */
  public function actionUpdate($id)
  {
    return $this->handleAjaxForm($this->findModel($id));
  }

  private function handleAjaxForm(Operator $model)
  {
    $model->ipTextarea = $this->ipsModelsToTextarea($model->operatorIp);
    if (
      Yii::$app->request->isAjax &&
      $model->load(Yii::$app->request->post()) &&
      $model->loadIps()
    ) {
      if (Yii::$app->request->post("submit")) {

        // TODO вынести транзакцию в модель
        $transaction = Yii::$app->db->beginTransaction();
        try {
          if ($model->save()) {
            $transaction->commit();
            return AjaxResponse::success();
          }
          $transaction->rollBack();
        } catch (Exception $e) {
          $transaction->rollBack();
          return AjaxResponse::error();
        }
      }
      Yii::$app->response->format = Response::FORMAT_JSON;
      return ActiveForm::validate($model);
    }

    return $this->renderAjax('form_modal', [
      'model' => $model,
      'countries' => Country::getDropdownItems(),
      'canChangeOperatorShowServiceUrl' => Yii::$app->getModule('promo')->canChangeOperatorShowServiceUrl(),
    ]);
  }

  /**
   * Finds the Operator model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Operator the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Operator::findOne($id)) !== null) {
      return $model;
    }
    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * @param $q
   * @return array
   */
  public function actionSelect2()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return Select2::getItems(new OperatorSearch());
  }

  /**
   * @Role({"root", "admin"})
   * @param $id
   * @return \yii\web\Response
   */
  public function actionEnable($id)
  {
    return AjaxResponse::set($this->findModel($id)->setEnabled()->save());
  }

  /**
   * @Role({"root", "admin"})
   * @param $id
   * @return \yii\web\Response
   */
  public function actionDisable($id)
  {
    return AjaxResponse::set($this->findModel($id)->setDisabled()->save());
  }

  public function ipsModelsToTextarea($models)
  {
    $str = '';
    foreach ($models as $model) {
      $str .= implode('/', [$model->from_ip, $model->mask]) . "\n";
    }
    return $str;
  }

  /**
   * Публичный метод к которому обращает виджет Editable
   * @param array $requestData
   * @return array
   */
  public function updateParams(array $requestData)
  {
    $operatorId = ArrayHelper::getValue($requestData, 'operatorId');
    $attribute = ArrayHelper::getValue($requestData, 'attribute');
    $operatorAttribute = ArrayHelper::getValue($requestData, $attribute);

    /** @var Operator $operator */
    $operator = Operator::findOne(['id' => $operatorId]);

    if ($operator === null) {
      return [
        'success' => false,
        'message' => Yii::_t('app.common.operation_failure'),
      ];
    }

    $operator->{$attribute} = $operatorAttribute;
    $saveResult = $operator->save();
    if (!$saveResult) {
      return [
        'success' => false,
        'message' => BaseHtml::errorSummary($operator)
      ];
    }

    return [
      'success' => true,
    ];
  }




}
