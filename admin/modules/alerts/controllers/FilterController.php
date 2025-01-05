<?php

namespace admin\modules\alerts\controllers;

use admin\modules\alerts\models\EventFilter;
use Exception;
use mcms\common\controller\AdminBaseController;
use mcms\common\helpers\ArrayHelper;
use rgk\utils\components\response\AjaxResponse;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Добавление, редактирование и удаление фильтров для правил
 * @package admin\modules\alerts\controllers
 */
class FilterController extends AdminBaseController
{
  const DEPENDENT_PARAM = 'depdrop_parents';
  const DEPENDENT_OUTPUT_PARAM = 'output';

  public $defaultAction = 'index';

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'delete' => ['POST'],
          'create' => ['POST'],
          'update-modal' => ['POST'],
          'get-values' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * Аякс редактирование фильтров
   * @param $id
   */
  public function actionUpdateModal($id)
  {
    $this->view->title = Yii::_t('alerts.event_filter.filter-update');
    $model = $this->findModel($id);
    if ($model->load(Yii::$app->request->post())) {
      if (!Yii::$app->request->post("submit")) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ActiveForm::validate($model);
      }
      return AjaxResponse::set($model->save());
    }
    return $this->renderAjax('form_filter', [
      'model' => $model
    ]);
  }

  /**
   * Добавление фильтра
   * @param $eventId
   */
  public function actionCreate($eventId)
  {
    $this->view->title = Yii::_t('alerts.event_filter.filter-add');
    $model = new EventFilter(['event_id' => $eventId]);
    if ($model->load(Yii::$app->request->post())) {
      if (!Yii::$app->request->post("submit")) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ActiveForm::validate($model);
      }
      return AjaxResponse::set($model->save());
    }

    return $this->renderAjax('form_filter', [
      'model' => $model
    ]);
  }

  /**
   * Удаление фильтра
   * @param $id
   */
  public function actionDelete($id)
  {
    $filter = $this->findModel($id);
    return AjaxResponse::set($filter->delete());
  }

  /**
   * Возвращаем список вариантов для фильтра
   */
  public function actionGetValues()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return [
      self::DEPENDENT_OUTPUT_PARAM => EventFilter::getFilterValues($this->getFilter())
    ];
  }

  /**
   * Получаем id фильтра из post данных
   * @throws Exception
   */
  private function getFilter()
  {
    $dep = Yii::$app->request->post(self::DEPENDENT_PARAM);
    if (!$dep) throw new Exception('Wrong request');

    $filter = ArrayHelper::getValue($dep, 0);
    if (!$filter) throw new Exception('Wrong request');

    return $filter;
  }

  /**
   * Finds the EventFilter model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return EventFilter the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = EventFilter::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
  }

}
