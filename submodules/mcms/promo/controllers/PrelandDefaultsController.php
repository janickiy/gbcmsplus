<?php

namespace mcms\promo\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use mcms\common\web\AjaxResponse;
use mcms\promo\models\Source;
use mcms\promo\models\Stream;
use Yii;
use mcms\promo\models\PrelandDefaults;
use mcms\promo\models\search\PrelandDefaultsSearch;
use mcms\common\controller\AdminBaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * PrelandDefaultsController implements the CRUD actions for PrelandDefaults model.
 */
class PrelandDefaultsController extends AdminBaseController
{

  use Translate;
  const LANG_PREFIX = "promo.preland-defaults.";
  //лимит для селекта потоков и источников
  const LIMIT = 10;

  /**
   * @inheritdoc
   */
  public $layout = '@app/views/layouts/main';

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
        ],
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function beforeAction($action)
  {
    $this->getView()->title = self::translate('main');
    return parent::beforeAction($action);
  }

  /**
   * Lists all PrelandDefaults models.
   * @return mixed
   */
  public function actionIndex()
  {
    $searchModel = new PrelandDefaultsSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    $select2Data = $this->getSelect2Data($searchModel);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
      'streamsData' => ArrayHelper::getValue($select2Data,'streams'),
      'sourcesData' => ArrayHelper::getValue($select2Data, 'sources'),
    ]);
  }


  /**
   * @param integer|null $id
   * @return array|string
   * @throws NotFoundHttpException
   */
  public function actionFormModal($id = null)
  {

    $model = $id ? $this->findModel($id) : new PrelandDefaults();
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      return Yii::$app->request->post("submit")
        ? AjaxResponse::set($model->save())
        : ActiveForm::validate($model);
    }

    $source = null;
    if (Yii::$app->request->get('source_id')
      && $source = Source::findOne((int)Yii::$app->request->get('source_id'))) {
      $model->source_id = $source->id;
    }

    return $this->renderAjax('form_modal', [
      'model' => $model,
      'source' => $source,
      'select2InitValues' => $this->getSelect2InitValues($model),
    ]);
  }


  /**
   * Deletes an existing PrelandDefaults model.
   * If deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   * @throws \Exception
   */
  public function actionDelete($id)
  {
    return AjaxResponse::set($this->findModel($id)->delete());
  }

  /**
   * Finds the PrelandDefaults model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return PrelandDefaults the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = PrelandDefaults::findOne($id)) !== null && Yii::$app->user->identity->canViewUser($model->user_id)) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * Возвращает начальные значения для select2 в виде отформатированной строки
   * @param PrelandDefaults $model
   * @return array
   */
  private function getSelect2InitValues(PrelandDefaults $model)
  {
    $select2InitValues = [];

    if ($model->stream_id) {
      $select2InitValues['stream'] = Stream::findOne($model->stream_id)->getStringInfo();
    }

    if ($model->source_id) {
      $select2InitValues['source'] = Source::findOne($model->source_id)->getStringInfo();
    }

    return $select2InitValues;
  }

  /**
   * Возвращает данные для select2 в фильтрации
   * @param PrelandDefaults $model
   * @return array
   */
  private function getSelect2Data(PrelandDefaults $model)
  {
    $select2Data = [];
    $streams = Stream::find()->where(['status' => Stream::STATUS_ACTIVE]);
    if (!Yii::$app->user->can('PromoViewOtherPeopleStreams')) {
      $streams->andWhere(['user_id' => Yii::$app->user->id]);
    }
    $streams = $streams->limit(self::LIMIT)->orderBy(['id' => SORT_DESC])->each();
    foreach ($streams as $stream) {
      $select2Data['streams'][$stream->id] = $stream->getStringInfo();
    }

    $sources = Source::find()->limit(self::LIMIT)->orderBy(['created_at' => SORT_DESC])->each();
    foreach ($sources as $source) {
      $select2Data['sources'][$source->id] = $source->getStringInfo();
    }

    return $select2Data;
  }


  /**
   * @param $id
   * @return array
   * @throws NotFoundHttpException
   */
  public function actionEnable($id)
  {
    return AjaxResponse::set($this->findModel($id)->setEnabled()->save());
  }


  /**
   * @param $id
   * @return array
   * @throws NotFoundHttpException
   */
  public function actionDisable($id)
  {
    return AjaxResponse::set($this->findModel($id)->setDisabled()->save());
  }
}