<?php

namespace mcms\promo\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\common\web\AjaxResponse;
use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\promo\models\PartnerProgram;
use mcms\promo\models\PartnerProgramItemForm as PartnerProgramItem;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * PartnerProgramItemsController implements the CRUD actions for LandingsSetItem model.
 */
class PartnerProgramItemsController extends AdminBaseController
{
  public $layout = '@app/views/layouts/main';

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

  public function actionCreateModal($partnerProgramId)
  {
    $this->view->title = Yii::_t(PartnerProgramItem::LANG_PREFIX . 'create-condition');

    $partnerProgram = $this->findPartnerProgramModel($partnerProgramId);

    return $this->handleSave(null, $partnerProgram);
  }

  public function actionUpdateModal($id)
  {
    $this->view->title = Yii::_t(PartnerProgramItem::LANG_PREFIX . 'update-condition');

    $item = $this->findModel($id);

    return $this->handleSave($item);
  }

  public function actionDelete($id)
  {
    return AjaxResponse::set($this->findModel($id)->delete() !== false);
  }

  /**
   * Finds the PartnerProgram model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return PartnerProgram the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findPartnerProgramModel($id)
  {
    if (($model = PartnerProgram::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * Finds the PartnerProgramItem model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return PartnerProgramItem the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = PartnerProgramItem::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * Обработать сохранение условия ПП
   * @param PartnerProgramItem $item
   * @param PartnerProgram $partnerProgram
   * @return array|string
   */
  private function handleSave(PartnerProgramItem $item = null, PartnerProgram $partnerProgram = null)
  {
    if (!$item) $item = new PartnerProgramItem;

    if ($item->isNewRecord) {
      $item->loadDefaultValues();
      $item->partner_program_id = $partnerProgram->id;
    }
    // Форма ввода
    if (!$item->load(Yii::$app->request->post())) {
      return $this->renderAjax('form', [
        'model' => $item,
        'exchangeCourses' => PartnerCurrenciesProvider::getInstance()->getCoursesAsArray(),
      ]);
    }

    // Валидация
    if (!Yii::$app->request->post("submit")) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      return ActiveForm::validate($item);
    }

    // Сохранение
    return AjaxResponse::set($item->save());
  }
}