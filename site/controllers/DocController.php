<?php

namespace site\controllers;

use Yii;
use yii\helpers\Url;

/**
 * Default controller for the `v3` module
 */
class DocController extends \bestyii\openapiReader\controllers\DefaultController
{
  public function actionIndex($uid = null)
  {
    
    $this->layout = '_clear';
    return $this->render('index', [
      'url' => Url::to(['api/doc/yaml'], true),
      'accessToken' => !is_null($uid) && class_exists('\grazio\user\models\UserIdentity') ? (\grazio\user\models\UserIdentity::findIdentity($uid))->access_token : null
    ]);
  }
  
  public function actionRedoc()
  {
    $this->layout = '_clear';
    return $this->render('redoc', [
      'url' => Url::to(['api/doc/json'], true),
    ]);
  }
  /**
   * Renders the index view for the module
   * @return string
   */
//    public function actionIndex()
//    {
//        return $this->render('index');
//    }
  public function actionJson()
  {
    $json = $this->getContent(Yii::$app->getModule('openapireader')->path)->toJson();
    if (is_callable(Yii::$app->getModule('openapireader')->afterRender)) {
      $json = call_user_func(Yii::$app->getModule('openapireader')->afterRender, $json);
    }
    return $json;
  }
  
  public function actionYaml()
  {
    $yaml = $this->getContent(Yii::$app->getModule('openapireader')->path)->toYaml();
    if (is_callable(Yii::$app->getModule('openapireader')->afterRender)) {
      $yaml = call_user_func(Yii::$app->getModule('openapireader')->afterRender, $yaml);
    }
    
    return $yaml;
  }
}
