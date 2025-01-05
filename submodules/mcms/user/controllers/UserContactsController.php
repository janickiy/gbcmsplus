<?php

namespace mcms\user\controllers;

use libs\NotFoundHttpException;
use mcms\common\controller\AdminBaseController;
use mcms\user\models\User;
use mcms\user\models\UserContact;
use rgk\utils\components\response\AjaxResponse;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class UserContactsController
 * @package mcms\user\controllers
 */
class UserContactsController extends AdminBaseController
{
    /**
     * @param $id
     * @return array|string
     * @throws NotFoundHttpException
     */
    public function actionCreateModal($id)
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException();
        }

        $model = new UserContact();
        $model->user_id = $id;

        return $this->handleModel($model);
    }

    /**
     * @param $id
     * @return array|string
     */
    public function actionUpdateModal($id)
    {
        return $this->handleModel($this->findModel($id));
    }

    /**
     * @param $id
     * @return array
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        return AjaxResponse::set($model->delete());
    }

    /**
     * @param UserContact $model
     * @return array|string
     */
    protected function handleModel($model)
    {
        $request = Yii::$app->request;

        if (!$model->load($request->post())) {
            return $this->renderAjax('form-modal', [
                'model' => $model,
            ]);
        }

        if ($request->post("submit")) {
            return AjaxResponse::set($model->save());
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        return ActiveForm::validate($model);
    }

    /**
     * @param $id
     * @return UserContact
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = UserContact::findOne($id);
        if ($model) {
            return $model;
        }

        throw new NotFoundHttpException();
    }
}