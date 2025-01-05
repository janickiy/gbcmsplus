<?php

namespace mcms\statistic\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\common\helpers\ArrayHelper;
use mcms\common\web\AjaxResponse;
use mcms\statistic\models\ColumnsTemplateNew as ColumnsTemplate;
use Yii;
use yii\db\ActiveRecord;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\widgets\ActiveForm;

/**
 * Шаблоны столбцов таблицы статистики
 */
class NewColumnTemplatesController extends AdminBaseController
{
  /**
   * Создание шаблона столбцов
   * @return string
   */
  public function actionCreate()
  {
    return $this->handleForm(new ColumnsTemplate());
  }

  /**
   * Редактирование шаблона столбцов
   * @param int $id
   * @return string
   */
  public function actionUpdate($id)
  {
    return $this->handleForm($this->findModel($id));
  }

  /**
   * Удалить шаблон столбцов
   * @param int $id
   * @return array
   */
  public function actionDelete($id)
  {
    $template = $this->findModel($id);
    ColumnsTemplate::setTemplate();

    return AjaxResponse::set($template->delete() !== false);
  }

  /**
   * Обработка формы для создание/редактирования шаблона столбцов
   * @param ColumnsTemplate $model
   * @return mixed
   */
  private function handleForm(ColumnsTemplate $model)
  {
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post("submit")) {
        return AjaxResponse::set($model->save());
      }
      return ActiveForm::validate($model);
    }
    return $this->renderAjax('form-modal', [
      'model' => $model
    ]);
  }

  /**
   * Находит модель шаблона столбцов, иначе возвращает 404 ошибку
   * @param $id
   * @return ColumnsTemplate|ActiveRecord
   * @throws NotFoundHttpException
   */
  private function findModel($id)
  {
    $model = ColumnsTemplate::findUserTemplates()->andWhere(['id' => $id])->one();
    if (!$model) throw new NotFoundHttpException('The requested page does not exist.');

    return $model;
  }

  /**
   * Отправляет обновленные данные по шаблонам столбцов
   * @return mixed
   */
  public function actionGetTemplate()
  {
    if (Yii::$app->request->isAjax) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      $columnsTemplateId = (int)Yii::$app->request->post('id');
      if ($columnsTemplateId) {
        return ColumnsTemplate::findOne($columnsTemplateId);
      }

      return ArrayHelper::index(ColumnsTemplate::getAllTemplates(), 'id');
    }
  }
}