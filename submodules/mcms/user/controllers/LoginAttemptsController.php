<?php

namespace mcms\user\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\user\models\LoginAttempt;
use mcms\user\models\search\LoginAttemptSearch;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * Class LoginAttemptsController
 * @package mcms\user\controllers
 */
class LoginAttemptsController extends AdminBaseController
{
    public function actionIndex()
    {
        $searchModel = new LoginAttemptSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $this->view->title = Yii::_t('users.login_logs.login_attempts');
        $this->setBreadcrumb($this->view->title, ['index'], false);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * @param $id
     * @return string
     */
    public function actionViewModal($id)
    {
        $model = $this->findModel($id);

        $this->view->title = Yii::_t('users.login_logs.login_attempt');

        return $this->renderAjax('view-modal', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return LoginAttempt
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if ($model = LoginAttempt::findOne($id)) {
            return $model;
        }

        throw new NotFoundHttpException();
    }
}