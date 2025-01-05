<?php

namespace mcms\promo\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\common\web\AjaxResponse;
use mcms\promo\components\events\landing_sets\LandingsAddedToSet;
use mcms\promo\components\events\landing_sets\LandingsRemovedFromSet;
use mcms\promo\components\landing_sets\LandingSetLandsUpdater;
use mcms\promo\components\LandingSetsNewLandsHandler;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingSet;
use mcms\promo\models\search\LandingSetItemSearch;
use mcms\promo\models\search\LandingSetSearch;
use mcms\promo\models\search\SourceSearch;
use mcms\promo\models\Source;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * LandingsController implements the CRUD actions for LandingsSet model.
 */
class LandingSetsController extends AdminBaseController
{
  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'delete' => ['POST'],
          'unlink-source' => ['POST'],
          'link-source' => ['POST'],
          'create-modal' => ['POST'],
          'update-landings' => ['POST'],
        ],
      ],
    ];
  }

  public $layout = '@app/views/layouts/main';

  /**
   * @param \yii\base\Action $action
   * @return bool
   * @throws \yii\web\ForbiddenHttpException
   */
  public function beforeAction($action)
  {
    $this->view->title = Yii::_t('promo.landing_sets.main');
    $this->setBreadcrumb($this->view->title, ['index'], false);

    return parent::beforeAction($action);
  }

  /**
   * Список наборов
   * @return string
   */
  public function actionIndex()
  {
    $searchModel = new LandingSetSearch;
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
    $dataProvider->query->with('category');

    if ($searchModel->landing_id) {
      $this->breadcrumbs[] = Yii::_t('promo.landing_sets.sets-includes-landing') . ' "' . Landing::findOne($searchModel->landing_id)->name . '"';
    }

    return $this->render('list', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel]);
  }

  /**
   * Создание набора.
   * Указание лендов и источников происходит на детальной странице
   * @return array|bool|Response|string
   */
  public function actionCreateModal()
  {
    $this->view->title = Yii::_t('promo.landing_sets.create');

    $model = new LandingSet;

    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      if (!Yii::$app->request->post("submit")) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ActiveForm::validate($model);
      }

      if ($model->insert()) {
        $this->flashSuccess('app.common.saved_successfully');

        return $this->redirect(['update', 'id' => $model->id]);
      }

      return AjaxResponse::error();
    }

    return $this->renderAjax('create', [
      'model' => $model,
    ]);
  }

  /**
   * Привязка источников к набору
   * @param $id
   * @return array|string
   */
  public function actionLinkSource($id)
  {
    $this->view->title = Yii::_t('promo.landing_sets.add_source');

    $model = $this->findModel($id);
    $model->scenario = LandingSet::SCENARIO_SOURCE_ADD;
    if ($model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      if (Yii::$app->request->post('submit')) {
        $source = Source::findOne(['id' => $model->source_id]);
        $source->link('landingSet', $model);

        return AjaxResponse::success();
      }

      return ActiveForm::validate($model);
    }

    return $this->renderAjax('add-source', [
      'model' => $model,
    ]);
  }

  /**
   * Отвязка источников от набора
   * @param $id
   * @return array
   */
  public function actionUnlinkSource($id)
  {
    $source = Source::findOne($id);
    $source->set_id = null;
    $source->save(false);
    return AjaxResponse::success();
  }

  /**
   * Обновить лендинги
   * @param $id
   * @return array
   */
  public function actionUpdateLandings($id)
  {
    (new LandingSetLandsUpdater($this->findModel($id), ['isForceUpdate' => true]))->run();
    return AjaxResponse::success();
  }

  /**
   * Изменение набора.
   * @param int $id ID набора
   * @return array|bool|string
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    $this->setBreadcrumb($model->name, [], false);
    $this->view->title = $model->name;

    if ($model->load(Yii::$app->request->post())) {
      if (!Yii::$app->request->post("submit")) {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ActiveForm::validate($model);
      }

      return AjaxResponse::set($model->update() !== false);
    }

    $landingsSearchModel = new LandingSetItemSearch;
    $landingsDataProvider = $landingsSearchModel->search(Yii::$app->request->get());
    $landingsDataProvider->query->andWhere(['set_id' => $model->id]);


    $sourcesSearchModel = new SourceSearch();
    $search[$sourcesSearchModel->formName()] = array_merge(
      Yii::$app->request->getQueryParam($sourcesSearchModel->formName()) ?: [], ['set_id' => $id]
    );
    $sourcesDataProvider = $sourcesSearchModel->search($search);
    $sourcesDataProvider->pagination = false;

    $sourcesDataProvider->sort->sortParam = 'sourceSort';

    return $this->render('update', [
      'model' => $model,
      'sourcesDataProvider' => $sourcesDataProvider,
      'sourcesSearchModel' => $sourcesSearchModel,
      'landingsSearchModel' => $landingsSearchModel,
      'landingsDataProvider' => $landingsDataProvider,
    ]);
  }

  /**
   * Удаление набора
   * @param int $id ID набора
   * @return array
   */
  public function actionDelete($id)
  {
    $model = $this->findModel($id);
    $model->unlinkAll('sources');
    if (!$model->delete()) {
      return AjaxResponse::error(Yii::_t('promo.landing_sets.delete-error'));
    }

    return AjaxResponse::success();
  }

  /**
   * Finds the Landing model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return LandingSet the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = LandingSet::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }
}