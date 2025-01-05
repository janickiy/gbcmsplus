<?php

namespace mcms\promo\controllers;


use mcms\common\controller\AdminBaseController;
use mcms\common\web\AjaxResponse;
use mcms\promo\models\UserPromoSetting;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Контроллер для виджета UserFakeWidget
 * Class UserFakeSettingsController
 * @package mcms\promo\controllers
 */
class UserFakeSettingsController extends AdminBaseController
{
  /**
   * @param $user_id
   * @return array
   */
  public function actionUpdate($user_id)
  {
    $model = UserPromoSetting::findOne(['user_id' => $user_id]) ?: new UserPromoSetting(['user_id' => $user_id]);

    if ($model->load(Yii::$app->request->post())) {
      if (!Yii::$app->request->post("submit")) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ActiveForm::validate($model);
      }
      return AjaxResponse::set($model->save());
    }
    return AjaxResponse::error();
  }
}