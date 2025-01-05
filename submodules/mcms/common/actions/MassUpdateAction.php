<?php

namespace mcms\common\actions;

use kartik\form\ActiveForm;
use mcms\common\web\AjaxResponse;
use Yii;
use yii\base\Action;
use yii\base\Model;
use yii\web\Response;

/**
 * Class MassUpdateAction
 * @package mcms\common\actions
 */
class MassUpdateAction extends Action
{
  /** @var Model */
  public $model;

  /**
   * @inheritdoc
   */
  public function run()
  {
    $selection = explode(',', Yii::$app->request->post('selection'));

    if (empty($selection)) {
      return AjaxResponse::error();
    }

    if (Yii::$app->request->isPost) {
      $this->model->load(Yii::$app->request->post());

      if (!Yii::$app->request->post('submit')) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ActiveForm::validate($this->model);
      }

      return AjaxResponse::set($this->model->save($selection));
    }
  }
}
