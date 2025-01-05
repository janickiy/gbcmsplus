<?php

namespace mcms\modmanager\controllers;

use mcms\common\web\AjaxResponse;
use Yii;
use mcms\modmanager\models\Module;
use mcms\modmanager\models\ModuleSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use mcms\common\controller\AdminBaseController;
use yii\helpers\ArrayHelper;
use yii\base\DynamicModel;

/**
 * ModulesController implements the CRUD actions for Module model.
 */
class ModulesController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';

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
   * Список установленных модулей.
   * @return mixed
   */
  public function actionIndex()
  {
    $this->getView()->title = Yii::_t('controller.modules_list');

    $searchModel = new ModuleSearch(['scenario' => 'search']);
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider
    ]);
  }

  /**
   * Установка модуля
   * @param $id
   * @return \yii\web\Response
   */
  public function actionInstall($id)
  {

    $availableModules = new Module();
    $availableModule = $availableModules->getModuleById($id);

    if (!$availableModule) {
      $this->flashSuccess('notifications.controller_manager_install_already_installed');
      return $this->redirect(['available-list']);
    }

    $model = new Module(['scenario' => Module::SCENARIO_INSTALL]);
    $model->module_id = $availableModule['id'];
    $model->name = $availableModule['name'];
    $model->is_disabled = 0;
    if ($model->save()) {
      return $this->redirect(['index']);
    }
  }

  /**
   * Finds the Module model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Module the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Module::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
  }

}
