<?php

namespace mcms\holds\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\holds\models\HoldProgram;
use mcms\holds\models\HoldProgramRuleSearch;
use mcms\holds\models\HoldProgramSearch;
use mcms\holds\models\LinkPartnerForm;
use mcms\user\Module;
use rgk\utils\actions\DeleteAjaxAction;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use mcms\common\web\AjaxResponse;

/**
 * Работа с правилами холдов для партнера
 */
class PartnerHoldRulesController extends AdminBaseController
{
  /**
   * @inheritdoc
   */
  public function actions()
  {
    return [
      'delete' => [
        'class' => DeleteAjaxAction::class,
        'modelClass' => HoldProgram::class,
      ],
    ];
  }

  /**
   * Список всех программ
   * @return string
   */
  public function actionIndex()
  {
    $this->view->title = Yii::_t('holds.main.hold_rules');

    $searchModel = new HoldProgramSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
    ]);
  }

  /**
   * Редактирование правила холда
   * @param $id
   * @return array|string
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);
    $this->view->title = Yii::_t('holds.main.update', ['rule_name' => $model->name]);

    /** @var Module $usersModule */
    $usersModule = Yii::$app->getModule('users');
    $userIds = ArrayHelper::getColumn($model->users, 'id');

    $ruleSearchModel = new HoldProgramRuleSearch(['hold_program_id' => $model->id]);
    $ruleDataProvider = $ruleSearchModel->search(Yii::$app->request->queryParams);
    $ruleDataProvider->sort->sortParam .= $ruleSearchModel->formName();

    /** @var \mcms\user\models\search\User $usersSearchModel */
    $usersSearchModel = $usersModule->api('user')->getSearchModel();
    $usersDataProvider = $usersSearchModel->search(Yii::$app->request->queryParams);
    $usersDataProvider->query->andWhere(['id' => $userIds]);
    $usersDataProvider->sort->sortParam .= $usersSearchModel->formName();
    $usersDataProvider->pagination->pageParam .= $usersSearchModel->formName();

    // Страница редактирования
    if (!$model->load(Yii::$app->request->post())) {
      return $this->render('update', [
        'model' => $model,
        'ruleSearchModel' => $ruleSearchModel,
        'ruleDataProvider' => $ruleDataProvider,
        'usersSearchModel' => $usersSearchModel,
        'usersDataProvider' => $usersDataProvider,
        'usersModule' => $usersModule,
      ]);
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    // Валидация данных
    if (!Yii::$app->request->post('submit')) {
      return ActiveForm::validate($model);
    }

    // Сохранение данных
    return AjaxResponse::set($model->update());
  }

  /**
   * Создание правила холда. Модалка
   * @return array|string|Response
   */
  public function actionCreateModal()
  {
    $this->view->title = Yii::_t('holds.main.create');

    $model = new HoldProgram();
    $request = Yii::$app->request;

    // Форма ввода
    if (!$model->load($request->post())) {
      return $this->renderAjax('create', [
        'model' => $model,
      ]);
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    // Валидация
    if (!$request->post('submit')) {
      return ActiveForm::validate($model);
    }

    // Сохранение
    if ($model->insert()) {
      $this->flashSuccess('app.common.saved_successfully');
      return $this->redirect(['update', 'id' => $model->id]);
    }

    return AjaxResponse::error();
  }

  /**
   * Прикрепление партнера
   * @param $id
   * @return array|string
   */
  public function actionLinkPartner($id)
  {
    $this->view->title = Yii::_t('holds.main.add-partner');
    $model = $this->findModel($id);

    $linkPartnerForm = new LinkPartnerForm();

    if ($linkPartnerForm->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post('submit') && $linkPartnerForm->validate()) {
        $result = $linkPartnerForm->setHoldProgramId($model->id);
        return AjaxResponse::set($result);
      }

      return ActiveForm::validate($linkPartnerForm);
    }

    return $this->renderAjax('add-partner', [
      'linkPartnerForm' => $linkPartnerForm,
      'model' => $model,
      'userModule' => Yii::$app->getModule('users'),
    ]);

  }

  /**
   * Открепление партнера
   * @param int $id
   * @return array
   */
  public function actionUnlinkPartner($id)
  {
    $linkPartnerForm = new LinkPartnerForm(['userId' => $id]);
    $result = $linkPartnerForm->unsetHoldProgramId();
    return AjaxResponse::set($result);
  }

  /**
   * Finds the HoldProgram model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return HoldProgram the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = HoldProgram::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
