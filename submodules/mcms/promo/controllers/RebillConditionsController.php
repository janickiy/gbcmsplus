<?php
namespace mcms\promo\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use mcms\common\web\AjaxResponse;
use mcms\common\widget\UserSelect2;
use mcms\promo\models\Landing;
use mcms\promo\models\Operator;
use mcms\promo\models\RebillCorrectConditions;
use mcms\promo\models\Source;
use mcms\promo\Module;
use Yii;
use mcms\common\controller\AdminBaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\widgets\ActiveForm;

class RebillConditionsController extends AdminBaseController
{
  use Translate;
  const LANG_PREFIX = "promo.rebill-conditions.";
  public $layout = '@app/views/layouts/main';

  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'delete' => ['post'],
        ],
      ],
    ];
  }

  public function beforeAction($action)
  {
    $this->getView()->title = self::translate('main');
    if (!$this->hasUserActionAccess()) {
      throw new NotFoundHttpException();
    }
    return parent::beforeAction($action);
  }

  public function actionIndex()
  {
    return $this->render('index');
  }

  protected function handleForm(RebillCorrectConditions $model, $isPersonal)
  {
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      if (!$model->created_by) $model->created_by = Yii::$app->user->id;
      if (Yii::$app->request->post("submit")) {
        return AjaxResponse::set($model->save());
      }
      return ActiveForm::validate($model);
    }
    return $this->renderAjax('form_modal', [
      'model' => $model,
      'isPersonal' => $isPersonal,
      'userModule' => Yii::$app->getModule('users'),
      'select2InitValues' => $this->getSelect2InitValues($model),
    ]);
  }

  public function actionCreateModal($partnerId = null)
  {
    return $this->handleForm(new RebillCorrectConditions(['partner_id' => $partnerId]), !!$partnerId);
  }

  public function actionUpdateModal($id, $isPersonal = false)
  {
    return $this->handleForm($this->findModel($id), $isPersonal);
  }

  public function actionDelete($id)
  {
    return AjaxResponse::set($this->findModel($id)->delete());
  }

  protected function getSelect2InitValues($model)
  {
    $select2InitValues = [];
    if ($model->landing_id) {
      $select2InitValues['landing_id'] = Landing::findOne($model->landing_id)->getStringInfo();
    }
    if ($model->operator_id) {
      $select2InitValues['operator_id'] = Operator::findOne($model->operator_id)->getStringInfo();
    }
    return $select2InitValues;
  }

  protected function findModel($id)
  {
    if (($model = RebillCorrectConditions::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * Функция проверяет есть ли у реселлера доступ к экшенам данного контроллера. В зависимости от настроек в promo модуле.
   * @return bool
   */
  private function hasUserActionAccess()
  {
    return Yii::$app->user->can(Module::PERMISSION_CAN_VIEW_REBILL_CONDITIONS_WIDGET);
  }
}