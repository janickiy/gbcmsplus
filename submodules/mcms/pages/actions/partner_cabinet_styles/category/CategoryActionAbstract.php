<?php

namespace mcms\pages\actions\partner_cabinet_styles\category;


use mcms\pages\models\PartnerCabinetStyleCategory;
use yii\base\Action;
use yii\web\NotFoundHttpException;
use Yii;
use yii\web\Response;
use mcms\common\web\AjaxResponse;
use yii\widgets\ActiveForm;

/**
 * Class Category
 * @package mcms\pages\actions\partner_cabinet_styles
 */
class CategoryActionAbstract extends Action
{

  /**
   * Finds the PartnerCabinetStyleCategory model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return PartnerCabinetStyleCategory the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModelStyleCategory($id)
  {
    if (($model = PartnerCabinetStyleCategory::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * @param PartnerCabinetStyleCategory $model
   * @return array|string
   */
  protected function handleCategoriesAjaxForm(PartnerCabinetStyleCategory $model)
  {
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post("submit")) {
        return AjaxResponse::set($model->save());
      }
      return ActiveForm::validate($model);
    }
    return $this->controller->renderAjax('categories-form-modal', [
      'model' => $model
    ]);
  }
}