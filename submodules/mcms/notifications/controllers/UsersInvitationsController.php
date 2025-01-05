<?php

namespace mcms\notifications\controllers;

use mcms\common\web\AjaxResponse;
use mcms\notifications\models\NotificationInvitationForm;
use mcms\notifications\models\UserInvitationEmail;
use mcms\notifications\models\UserInvitationEmailSent;
use mcms\notifications\models\search\UsersInvitationsEmailsSearch;
use mcms\notifications\models\search\UsersInvitationsEmailsSentSearch;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class NotificationsController
 * @package mcms\notifications\controllers
 */
class UsersInvitationsController extends BaseNotificationsController
{
  /**
   * @return string
   */
  public function actionIndex()
  {
    $searchModel = new UsersInvitationsEmailsSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider
    ]);
  }

  /**
   * @return array|string
   */
  public function actionCreateModal()
  {
    $form = new NotificationInvitationForm(new UserInvitationEmail());

    if ($form->load(Yii::$app->request->post())) {
      return $this->handleForm($form);
    }

    return $this->renderAjax('form-modal', [
      'model' => $form,
      'replacementsDataProvider' => $form->getReplacementsDataProvider(),
    ]);
  }

  /**
   * @param $id
   * @return array|string
   */
  public function actionUpdateModal($id)
  {
    $form = new NotificationInvitationForm($this->findModel($id));

    if ($form->load(Yii::$app->request->post())) {
      return $this->handleForm($form);
    }

    return $this->renderAjax('form-modal', [
      'model' => $form,
      'replacementsDataProvider' => $form->getReplacementsDataProvider(),
    ]);
  }

  /**
   * @param $id
   * @return array
   */
  public function actionDelete($id)
  {
    $model = $this->findModel($id);

    if ($model->delete()) {
      return AjaxResponse::success();
    }

    return AjaxResponse::error();
  }

  /**
   * @return string
   */
  public function actionSent()
  {
    $searchModel = new UsersInvitationsEmailsSentSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('sent', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider
    ]);
  }

  /**
   * @param $id
   * @return string
   * @throws NotFoundHttpException
   */
  public function actionSentView($id)
  {
    $model = UserInvitationEmailSent::findOne($id);
    if (!$model) {
      throw new NotFoundHttpException();
    }

    return $this->renderAjax('sent-view', [
      'model' => $model,
    ]);
  }

  /**
   * @param $id
   * @return UserInvitationEmail
   * @throws NotFoundHttpException
   */
  protected function findModel($id)
  {
    if ($model = UserInvitationEmail::findOne($id)) {
      return $model;
    }

    throw new NotFoundHttpException();
  }

  /**
   * @param NotificationInvitationForm $form
   * @return array
   */
  protected function handleForm($form)
  {
    if (Yii::$app->request->post('submit') && $form->save()) {
      return AjaxResponse::success();
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    return ActiveForm::validate($form);
  }
}