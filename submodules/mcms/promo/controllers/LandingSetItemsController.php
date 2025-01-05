<?php

namespace mcms\promo\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\common\web\AjaxResponse;
use mcms\promo\models\LandingSet;
use mcms\promo\models\LandingSetItemForm as LandingSetItem;
use Yii;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * LandingSetItemsController implements the CRUD actions for LandingsSetItem model.
 */
class LandingSetItemsController extends AdminBaseController
{
  public $layout = '@app/views/layouts/main';

  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'enable' => ['POST'],
          'disable' => ['POST'],
          'delete' => ['POST'],
        ],
      ],
    ];
  }

  public function actionCreateModal($setId)
  {
    $this->view->title = Yii::_t('promo.landing_set_items.add-landing');

    $set = $this->findSetModel($setId);
    $this->checkAutoupdate($set);

    return $this->handleSave(null, $set);
  }

  public function actionUpdateModal($id)
  {
    $this->view->title = Yii::_t('promo.landing_set_items.edit-landing');

    $setItem = $this->findModel($id);
    $landingsSet = $setItem->set;
    $this->checkAutoupdate($landingsSet);

    return $this->handleSave($setItem, $landingsSet);
  }

  public function actionDelete($id)
  {
    $setItem = $this->findModel($id);
    $this->checkAutoupdate($setItem->set);

    return AjaxResponse::set($setItem->delete() !== false);
  }

  public function actionEnable($id)
  {
    $setItem = $this->findModel($id);
    $this->checkAutoupdate($setItem->set);

    return AjaxResponse::set($setItem->setEnabled()->save());
  }

  public function actionDisable($id)
  {
    $setItem = $this->findModel($id);
    $this->checkAutoupdate($setItem->set);

    return AjaxResponse::set($setItem->setDisabled()->save());
  }

  /**
   * Наборы с автообновлением нельзя редактировать вручную
   * @param LandingSet $set
   * @throws ForbiddenHttpException
   */
  protected function checkAutoupdate(LandingSet $set)
  {
    if (!$set->canManageManual()) {
      throw new ForbiddenHttpException;
    }
  }

  /**
   * Finds the Landing model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return LandingSet the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findSetModel($id)
  {
    if (($model = LandingSet::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * Finds the Landing model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return LandingSetItem the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = LandingSetItem::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }

  private function handleSave(LandingSetItem $modelItem = null, LandingSet $modelSet)
  {
    // Инициализация
    if (!$modelItem) $modelItem = new LandingSetItem;

    if ($modelItem->isNewRecord) {
      $modelItem->loadDefaultValues();
      $modelItem->set_id = $modelSet->id;
    }

    // Форма ввода
    if (!$modelItem->load(Yii::$app->request->post())) {
      return $this->renderAjax('form', [
        'modelItem' => $modelItem,
        'modelSet' => $modelSet,
      ]);
    }

    // Валидация
    if (!Yii::$app->request->post("submit")) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      return ActiveForm::validate($modelItem);
    }

    // Сохранение
    return AjaxResponse::set($modelItem->save());
  }
}