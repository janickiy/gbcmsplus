<?php

namespace mcms\notifications\controllers;

use mcms\notifications\models\Notification;
use mcms\notifications\models\NotificationsDelivery;
use mcms\notifications\models\search\NotificationsDeliverySearch;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class DeliveryController extends BaseNotificationsController
{
  public $layout = '@app/views/layouts/main';

  public function actionIndex()
  {
    $this->getView()->title = Yii::_t('notifications.menu.delivery');

    $searchModel = new NotificationsDeliverySearch();
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    if (!Yii::$app->user->can('NotificationsDeliveryNotOwn')) {
      // Только свои TRICKY Если переместить в Search модель, то уведомления не будут отображаться даже партнеру
      $dataProvider->query->andWhere(['user_id' => Yii::$app->user->id]);
    }

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'modules' => $this->_getModulesEventList(),
      'notificationTypes' => ArrayHelper::map(Notification::$notificationTypeList, 'id', function ($el) {
        return Yii::_t($el['title']);
      })
    ]);
  }

  public function actionView($id)
  {
    $model = $this->findModel($id);
    if (!$model->hasAccess()) throw new NotFoundHttpException;

    return $this->renderAjax('view', [
      'model' => $model
    ]);
  }

  protected function findModel($id)
  {
    if (($model = NotificationsDelivery::findOne($id)) !== null) {
      return $model;
    }
    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
