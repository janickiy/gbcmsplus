<?php

namespace mcms\logs\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\logs\models\Logs;
use Yii;
use mcms\logs\models\LogsSearch;


class DefaultController extends AdminBaseController
{

  public $controllerTitle = 'Logs';
  public $layout = '@admin/views/layouts/main';

  public function beforeAction($action)
  {
    $this->controllerTitle = Yii::_t('logs.main.logs');
    return parent::beforeAction($action);
  }

  public function actionIndex()
  {
    $searchModel = new LogsSearch();
    $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

    return $this->render('index', [
      'dataProvider'=>$dataProvider,
      'searchModel'=>$searchModel
    ]);
  }

  public function actionViewModal($id)
  {
    $model = Logs::findOne($id);

    //TODO Пересмотреть формат логирования в некоторых событиях
    $data = json_decode($model->EventData, true);
    $replaced = [];
    foreach($data as $k => $v){
      if(is_array($v)){
        $v = json_encode($v, JSON_UNESCAPED_UNICODE);
      }
      $replaced[preg_replace('/{|}/','',$k)] = $v;
    }


    return $this->renderAjax('_modal', [
      'data' => $replaced
    ]);
  }

}