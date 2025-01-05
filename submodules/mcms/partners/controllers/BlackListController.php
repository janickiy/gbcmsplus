<?php

namespace mcms\partners\controllers;

use mcms\common\controller\SiteBaseController as BaseController;
use mcms\common\helpers\curl\Curl;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class BlackListController extends BaseController
{
  public function actionIndex($hash)
  {
    $blackListApiUrl = ArrayHelper::getValue(Yii::$app->params, ['provider_blacklist', $hash]);
    if ($blackListApiUrl === null) {
      throw new NotFoundHttpException();
    }

    $curl = new Curl(['url' => $blackListApiUrl]);
    $result = $curl->getResult();

    if ((int) $curl->curlInfo['http_code'] !== 200) {
      throw new NotFoundHttpException();
    }

    return $result;
  }
}
