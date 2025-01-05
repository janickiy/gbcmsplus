<?php

namespace mcms\holds\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\holds\models\HoldProgramRule;
use rgk\utils\actions\DeleteAjaxAction;
use rgk\utils\actions\UpdateModalAction;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use mcms\common\web\AjaxResponse;

/**
 * Работа элементами правила
 */
class PartnerHoldRuleItemsController extends AdminBaseController
{
  /**
   * @inheritdoc
   */
  public function actions()
  {
    return [
      'delete' => [
        'class' => DeleteAjaxAction::class,
        'modelClass' => HoldProgramRule::class,
      ],
      'update-modal' => [
        'class' => UpdateModalAction::class,
        'modelClass' => HoldProgramRule::class,
        'formView' => 'add-rule'
      ],
    ];
  }

  /**
   * Создание
   * @param $hold_program_id
   * @return array|string|Response
   */
  public function actionCreateModal($hold_program_id)
  {
    $model = new HoldProgramRule(['hold_program_id' => $hold_program_id, 'unhold_range' => 1, 'min_hold_range' => 1]);

    // Форма ввода
    if (!$model->load(Yii::$app->request->post())) {
      return $this->renderAjax('add-rule', [
        'model' => $model,
      ]);
    }
    Yii::$app->response->format = Response::FORMAT_JSON;
    // Валидация
    if (!Yii::$app->request->post('submit')) {
      return ActiveForm::validate($model);
    }

    return AjaxResponse::set($model->save());
  }
}
