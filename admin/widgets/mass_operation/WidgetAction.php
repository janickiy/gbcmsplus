<?php

namespace admin\widgets\mass_operation;

use mcms\common\web\AjaxResponse;
use Yii;
use yii\base\Action;
use yii\web\Response;
use yii\widgets\ActiveForm;

class WidgetAction extends Action
{
  /** @var ModelInterface|yii\base\Model */
  public $model;

  public function run()
  {
    if (Yii::$app->request->isPost) {
      $this->model->load(Yii::$app->request->post());
      if (!Yii::$app->request->post('submit')) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ActiveForm::validate($this->model);
      }

      return AjaxResponse::set($this->model->save());
    }
  }
}
