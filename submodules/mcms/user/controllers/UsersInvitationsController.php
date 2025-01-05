<?php

namespace mcms\user\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\common\helpers\Select2;
use mcms\user\models\search\UsersInvitationsSearch;
use mcms\user\models\UserInvitation;
use mcms\user\models\UserInvitationForm;
use rgk\utils\components\response\AjaxResponse;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class UsersInvitationsController
 * @package mcms\user\controllers
 */
class UsersInvitationsController extends AdminBaseController
{
    /**
     *
     */
    public function actionIndex()
    {
        $searchModel = new UsersInvitationsSearch();
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
        $form = new UserInvitationForm();
        $request = Yii::$app->request;

        if ($form->load($request->post())) {
            if ($request->post('submit') && $form->save()) {
                return AjaxResponse::success();
            }

            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($form);
        }

        return $this->renderAjax('create-modal', [
            'model' => $form,
        ]);
    }

    /**
     * @param $id
     * @return array|string
     */
    public function actionUpdateModal($id)
    {
        $model = $this->findModel($id);
        $request = Yii::$app->request;

        if ($model->load($request->post())) {
            if ($request->post('submit') && $model->save()) {
                return AjaxResponse::success();
            }

            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        return $this->renderAjax('update-modal', [
            'model' => $model
        ]);
    }

    /**
     * @Description("Search invitations from select2")
     * @param $q
     * @return array
     */
    public function actionSelect2()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return Select2::getItems(new UsersInvitationsSearch());
    }

    /**
     * @param $id
     * @return array
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!$model->delete()) {
            return AjaxResponse::error();
        }

        return AjaxResponse::success();
    }

    /**
     * @param $id
     * @return UserInvitation
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if ($model = UserInvitation::findOne($id)) {
            return $model;
        }

        throw new NotFoundHttpException();
    }
}