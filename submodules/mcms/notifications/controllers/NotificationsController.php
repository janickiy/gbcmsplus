<?php

namespace mcms\notifications\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\notifications\models\BrowserNotification;
use mcms\notifications\models\EmailNotification;
use mcms\notifications\models\Notification;
use mcms\notifications\models\NotificationCreationForm;
use mcms\notifications\components\api\BrowserNotificationSetViewed;
use mcms\common\web\AjaxResponse;
use mcms\notifications\models\PushNotification;
use mcms\notifications\models\search\BrowserNotificationSearch;
use mcms\notifications\models\search\EmailNotificationSearch;
use mcms\notifications\models\search\PushNotificationSearch;
use mcms\notifications\models\search\TelegramNotificationSearch;
use mcms\notifications\models\TelegramNotification;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

/**
 * Class NotificationsController
 * @package mcms\notifications\controllers
 */
class NotificationsController extends BaseNotificationsController
{

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'image-upload' => ['post'],
        ],
      ],
    ];
  }

  /**
   * @return array|string
   * @throws \yii\base\Exception
   */
  public function actionImageUpload()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $uploadedFile = UploadedFile::getInstanceByName('file');

    if (!$uploadedFile) {
      return 'Upload error!';
    }

    $uploadPath = Yii::getAlias('@uploadPath/' . $this->module->id . '/' . $this->id . '/');
    $uploadUrl = Yii::$app->getModule('partners')->getFilledServerNameForEmail() . '/uploads/' . $this->module->id . '/' . $this->id . '/';

    $fileName = sprintf('%s.%s', Yii::$app->security->generateRandomString(10), $uploadedFile->getExtension());

    FileHelper::createDirectory($uploadPath);
    $uploadedFile->saveAs($uploadPath . $fileName);

    return ['location' => $uploadUrl . $fileName];
  }

  /**
   * @return array|string
   */
  public function actionCreate()
  {
    $this->view->title = Yii::_t('main.create_notifications');
    return $this->handleNotificationForm(new NotificationCreationForm());
  }

  /**
   * @param NotificationCreationForm $form
   * @return array|string
   */
  private function handleNotificationForm(NotificationCreationForm $form)
  {
    if ($form->load(Yii::$app->request->post())) {
      if (!Yii::$app->user->can('NotificationsDeliveryTest')) {
        $form->isTest = false;
      }

      if (!Yii::$app->request->post('submit')) {
        // Валидация
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ActiveForm::validate($form);
      } else {
        // Сохранение
        $form->triggerEvent();
        return AjaxResponse::success(['isTest' => (bool)$form->isTest]);
      }
    }

    return $this->renderAjax('notificationCreationForm', [
      'model' => $form,
      'replacementsDataProvider' => $form->getReplacementsDataProvider(),
      'notificationTypes' => json_encode(
        ArrayHelper::map(Notification::$notificationTypeList, 'name', 'id')
      )
    ]);
  }

  /**
   * @return array
   */
  public function actionReadAll()
  {
    (new BrowserNotificationSetViewed(['user_id' => Yii::$app->user->id]))->getResult();
    return AjaxResponse::success();
  }

  /**
   * @return string
   */
  public function actionEmail()
  {
    $this->getView()->title = Yii::_t('notifications.main.email_notifications');

    $searchModel = new EmailNotificationSearch;
    $searchParams = Yii::$app->request->getQueryParam($searchModel->formName());
    $dataProvider = $searchModel->search([$searchModel->formName() => $searchParams]);

    if (!Yii::$app->user->can('NotificationsNotificationsEmailNotOwn')) {
      // Только свои TRICKY Если переместить в Search модель, то уведомления не будут отображаться даже партнеру
      $dataProvider->query->andOnCondition([
        'or',
        [EmailNotification::tableName() . '.from_user_id' => null],
        [EmailNotification::tableName() . '.from_user_id' => Yii::$app->user->id],
      ]);
    }

    return $this->render('email', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
    ]);
  }

  /**
   * @return string
   */
  public function actionBrowser()
  {
    $this->getView()->title = Yii::_t('notifications.main.browser_notifications');

    $searchModel = new BrowserNotificationSearch;
    $searchParams = Yii::$app->request->getQueryParam($searchModel->formName());
    $dataProvider = $searchModel->search([$searchModel->formName() => $searchParams]);

    if (!Yii::$app->user->can('NotificationsNotificationsBrowserNotOwn')) {
      // Только свои TRICKY Если переместить в Search модель, то уведомления не будут отображаться даже партнеру
      $dataProvider->query->andWhere(['or',
        [BrowserNotification::tableName() . '.from_user_id' => Yii::$app->user->id],
        [BrowserNotification::tableName() . '.user_id' => Yii::$app->user->id],
      ]);
    }

    return $this->render('browser', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'modules' => $this->_getModulesEventList(),
    ]);
  }

  /**
   * @return string
   */
  public function actionTelegram()
  {
    $this->getView()->title = Yii::_t('notifications.main.telegram_notifications');

    $searchModel = new TelegramNotificationSearch;
    $searchParams = Yii::$app->request->getQueryParam($searchModel->formName());
    $dataProvider = $searchModel->search([$searchModel->formName() => $searchParams]);

    if (!Yii::$app->user->can('NotificationsNotificationsTelegramNotOwn')) {
      // Только свои TRICKY Если переместить в Search модель, то уведомления не будут отображаться даже партнеру
      $dataProvider->query->andWhere(['or',
        [TelegramNotification::tableName() . '.from_user_id' => Yii::$app->user->id],
        [TelegramNotification::tableName() . '.user_id' => Yii::$app->user->id],
      ]);
    }

    return $this->render('telegram', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel
    ]);
  }

  /**
   * @return string
   */
  public function actionPush()
  {
    $this->getView()->title = Yii::_t('notifications.main.push_notifications');

    $searchModel = new PushNotificationSearch();
    $searchParams = Yii::$app->request->getQueryParam($searchModel->formName());
    $dataProvider = $searchModel->search([$searchModel->formName() => $searchParams]);

    if (!Yii::$app->user->can('NotificationsNotificationsPushNotOwn')) {
      // Только свои TRICKY Если переместить в Search модель, то уведомления не будут отображаться даже партнеру
      $dataProvider->query->andWhere(['or',
        [PushNotification::tableName() . '.from_user_id' => Yii::$app->user->id],
        [PushNotification::tableName() . '.user_id' => Yii::$app->user->id],
      ]);
    }

    return $this->render('push', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel
    ]);
  }

  /**
   * @param $id
   * @return string
   * @throws NotFoundHttpException
   */
  public function actionEmailViewModal($id)
  {
    $model = EmailNotification::findOne($id);
    if (!$model->hasAccess()) throw new NotFoundHttpException;

    return $this->renderAjax('viewModal', [
      'model' => $model
    ]);
  }

  /**
   * @param $id
   * @return string
   * @throws NotFoundHttpException
   */
  public function actionBrowserViewModal($id)
  {
    $model = BrowserNotification::findOne($id);
    if (!$model->hasAccess()) throw new NotFoundHttpException;

    return $this->renderAjax('viewModal', [
      'model' => $model
    ]);
  }
}