<?php

namespace admin\modules\alerts\controllers;

use admin\modules\alerts\models\Event;
use admin\modules\alerts\models\search\EventFilterSearch;
use admin\modules\alerts\models\search\EventSearch;
use mcms\common\controller\AdminBaseController;
use rgk\utils\components\response\AjaxResponse;
use yii\web\Response;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\widgets\ActiveForm;

/**
 * Создание, редактирование, удаление правил для алертов
 * @package admin\modules\alerts\controllers
 */
class DefaultController extends AdminBaseController
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
                ],
            ],
        ];
    }

    /**
     * Список всех правил
     */
    public function actionIndex()
    {
        $this->view->title = Yii::_t('alerts.main.rule-list');
        $searchModel = new EventSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Модалка для создания правила
     */
    public function actionCreate()
    {
        $this->view->title = Yii::_t('alerts.main.add');
        $model = new Event();
        if ($model->load(Yii::$app->request->post())) {
            if (!Yii::$app->request->post("submit")) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            return $model->save()
                ? $this->redirect(['update', 'id' => $model->id])
                : AjaxResponse::error();
        }

        $model->loadDefaultValues();
        return $this->renderAjax('create', [
            'model' => $model
        ]);
    }

    /**
     * Редактирование правила
     * @param $id
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->view->title = $model->name;
        if ($model->load(Yii::$app->request->post())) {
            if (!Yii::$app->request->post("submit")) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            return $model->save()
                ? $this->redirect(['index'])
                : AjaxResponse::error();
        }

        $filtersSearchModel = new EventFilterSearch(['event_id' => $id]);
        $filtersDataProvider = $filtersSearchModel->search(Yii::$app->request->queryParams);

        return $this->render('update', [
            'model' => $model,
            'filtersDataProvider' => $filtersDataProvider
        ]);
    }

    /**
     * Деактивация
     * @param $id
     * @return array
     */
    public function actionDisable($id)
    {
        $model = $this->findModel($id);
        $model->setDisabled();
        return AjaxResponse::set($model->save());
    }

    /**
     * Активация
     * @param $id
     * @return array
     */
    public function actionEnable($id)
    {
        $model = $this->findModel($id);
        $model->setEnabled();
        return AjaxResponse::set($model->save());
    }

    /**
     * Удаление правила
     * @param $id
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        return AjaxResponse::set($model->delete());
    }

    /**
     * Finds the Event model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Event the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Event::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
