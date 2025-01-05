<?php

namespace mcms\promo\controllers;

use mcms\common\exceptions\ModelNotSavedException;
use mcms\common\traits\Translate;
use mcms\promo\models\Domain;
use mcms\promo\models\SmartLink;
use mcms\promo\models\Stream;
use mcms\promo\models\UserPromoSetting;
use Yii;
use mcms\promo\models\Source;
use mcms\promo\models\search\SourceSearch;
use mcms\common\controller\AdminBaseController;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\base\Exception;
use yii\widgets\ActiveForm;

/**
 * Контроллер смарт ссылок
 */
class SmartLinksController extends AdminBaseController
{

  use Translate;

  public $layout = '@app/views/layouts/main';

  /**
   *
   */
  const LANG_PREFIX = "promo.smart_links.";

  const EDITABLE_COLUMN_EMPTY_MESSAGE = '';
  const EDITABLE_COLUMN_OPERATORS_GLUE = ', ';

  /**
   * Lists all Source models.
   * @return mixed
   */
  public function actionIndex()
  {
    $searchModel = new SourceSearch([
      'source_type' => SourceSearch::SOURCE_TYPE_SMART_LINK,
    ]);

    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    $streams = Stream::find()->where(['status' => Stream::STATUS_ACTIVE]);
    if (!Yii::$app->user->can('PromoViewOtherPeopleStreams')) {
      $streams->andWhere(['user_id' => Yii::$app->user->id]);
    }

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'streamNamesData' => ArrayHelper::map($streams->each(), 'id', 'name')
    ]);
  }


  /**
   * Displays a single Source model.
   * @return mixed
   */
  public function actionView()
  {
    $modelId = Yii::$app->request->isAjax ? Yii::$app->request->post('expandRowKey') : Yii::$app->request->get('id');
    $model = $this->findModel($modelId);

    return Yii::$app->request->isAjax
      ? $this->renderAjax('_view', ['model' => $model])
      : $this->render('view', ['model' => $model]);
  }

  /**
   * @param $id
   * @return array|string|Response
   * @throws Exception
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);
    $model->setScenario(SmartLink::SCENARIO_ADMIN_UPDATE_SMART_LINK);

    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      return ActiveForm::validate($model);
    }

    if ($model->load(Yii::$app->request->post()) && $model->validate()) {

      if (!$model->save(false)) {
        throw new ModelNotSavedException();
      }
      $this->flashSuccess('promo.arbitrary_sources.source_saved');
      return $this->redirect(['update', 'id' => $model->id]);
    }

    return $this->render('form', [
      'model' => $model,
      'domainDropDownItems' => Domain::getUserActiveDomainItems($model->user_id, $model->domain_id),
      'currency' => Yii::$app->getModule('payments')->api('getUserCurrency', ['userId' => $model->user->id])->getResult(),
      'globalPostbackUrl' => UserPromoSetting::getGlobalPostbackUrl($model->user_id),
      'globalComplainsPostbackUrl' => UserPromoSetting::getGlobalComplainsPostbackUrl($model->user_id),
    ]);

  }

  /**
   * @param integer $id
   * @return Source the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = SmartLink::findOne(['id' => $id, 'source_type' => Source::SOURCE_TYPE_SMART_LINK])) !== null
      && Yii::$app->user->identity->canViewUser($model->user_id)
    ) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }


}
