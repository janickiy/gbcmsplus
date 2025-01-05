<?php

namespace mcms\pages\actions\partner_cabinet_styles\fields;

use mcms\common\web\AjaxResponse;
use mcms\pages\models\PartnerCabinetStyleCategory;
use mcms\pages\models\PartnerCabinetStyleField;
use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class FieldActionAbstract
 * @package mcms\pages\actions\partner_cabinet_styles\fields
 */
class FieldActionAbstract extends Action
{

  /**
   * @param PartnerCabinetStyleField $model
   * @return array|string
   */
  protected function handleAjaxForm(PartnerCabinetStyleField $model)
  {

    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post("submit")) {
        return AjaxResponse::set($model->save());
      }
      return ActiveForm::validate($model);
    }

    return $this->controller->renderAjax('field-form-modal', [
      'model' => $model,
      'categories' => $this->getCategories(),
    ]);

  }

  /**
   * @param $id
   * @return PartnerCabinetStyleField
   * @throws NotFoundHttpException
   */
  protected function findModel($id)
  {
    if (($model = PartnerCabinetStyleField::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * @return PartnerCabinetStyleCategory[]
   */
  protected function getCategories()
  {
    return PartnerCabinetStyleCategory::find()->all();
  }
}