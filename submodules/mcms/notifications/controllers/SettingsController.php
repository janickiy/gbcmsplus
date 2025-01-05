<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 8/24/15
 * Time: 4:13 PM
 */

namespace mcms\notifications\controllers;

use mcms\common\web\AjaxResponse;
use mcms\notifications\models\NotificationsIgnore;
use mcms\notifications\models\search\MyNotificationSearch;
use mcms\notifications\models\search\NotificationSearch;
use mcms\notifications\models\UserPushToken;
use Yii;
use mcms\common\controller\AdminBaseController;
use mcms\common\event\Event;
use mcms\notifications\models\Notification;
use mcms\notifications\Module;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\base\Model;

/**
 * Class SettingsController
 * @package mcms\notifications\controllers
 */
class SettingsController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';


  private function _getModulesEventList()
  {
    $modulesEventList = [];
    $enabledModules = Yii::$app->getModules();

    if (count($enabledModules)) foreach ($enabledModules as $enabledModule) {
      $moduleId = ArrayHelper::getValue($enabledModule, 'id');
      $moduleConfig = Yii::$app->getModule($moduleId);
      if (empty($moduleConfig->events)) continue;

      $modulesEventList[$moduleId] = [
        'id'     => $moduleId,
        'db_id'  => ArrayHelper::getValue($enabledModule, 'id'),
        'name'   => ArrayHelper::getValue($enabledModule, 'name'),
        'events' => $moduleConfig->events
      ];
    }

    return $modulesEventList;
  }

  /**
   * @return string
   */
  public function actionList()
  {
    $this->view->title = Yii::_t('controllers.modules_list');

    return $this->render('list', [
      'modules' => $this->_getModulesEventList()
    ]);
  }

  /**
   * Настройка личных уведомлений пользователя в админке
   * @return string
   */
  public function actionMyNotifications()
  {
    $this->view->title = Yii::_t('notifications.menu.my-notifications');

    $searchModel = new MyNotificationSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    $userParams = Yii::$app->getModule('users')
      ->api('userParams', ['userId' =>  Yii::$app->user->id])
      ->getResult();

    $modules = \mcms\modmanager\models\Module::find()->select('id,module_id')->each();
    return $this->render('my-notifications', [
      'moduleId' => ArrayHelper::getValue($searchModel, 'module_id', 0),
      'moduleItems' => ArrayHelper::map($modules, 'id', 'module_id'),
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'telegramId' => ArrayHelper::getValue($userParams, 'telegram_id'),
      'isTelegramConfigured' => Yii::$app->getModule('notifications')->isTelegramConfigured(),
      'isPushEnabled' => UserPushToken::isUserHaveToken(Yii::$app->user->id)
    ]);
  }

  /**
   * Включить уведомление
   * @return array
   */
  public function actionMyNotificationsEnable()
  {
    if (Yii::$app->request->isAjax) {
      $id = (int)Yii::$app->request->get('id');
      $moduleId = Yii::$app->request->get('module_id');
      $type = Yii::$app->request->get('type');

      $condition = [
        'user_id' => Yii::$app->user->id,
      ];

      if ($id) {
        $condition['notification_id'] = $id;
      }

      if (!$id && $type !== null) {
        NotificationsIgnore::noticeAll($condition['user_id'], $type, $moduleId);
      } else {
        NotificationsIgnore::deleteAll($condition);
      }

      Yii::$app->response->format = Response::FORMAT_JSON;
      return AjaxResponse::success();
    }
  }

  /**
   * Выключить уведомление
   * @return array
   */
  public function actionMyNotificationsDisable()
  {
    if (Yii::$app->request->isAjax) {
      $id = (int)Yii::$app->request->get('id');
      $moduleId = Yii::$app->request->get('module_id');
      $type = Yii::$app->request->get('type');

      $condition = [
        'user_id' => Yii::$app->user->id,
      ];

      if ($id) {
        $condition['notification_id'] = $id;
      }

      if (!$id && $type !== null) {
        NotificationsIgnore::ignoreAll($condition['user_id'], $type, $moduleId);
      } else {
        $ni = new NotificationsIgnore($condition);
        $ni->save();
      }

      Yii::$app->response->format = Response::FORMAT_JSON;
      return AjaxResponse::success();
    }
  }

  /**
   * Подписка на пуш уведомления
   * @return array
   */
  public function actionSubscribePush()
  {
    $token = Yii::$app->request->post('token');
    $model = $this->findByToken($token)->andWhere(['user_id' => Yii::$app->user->id])->one();
    if ($model) return AjaxResponse::set(true);
    $model = new UserPushToken(['user_id' => Yii::$app->user->id, 'token' => $token]);
    return AjaxResponse::set($model->save());
  }

  /**
   * Отписка от пуш уведомлений
   * @return bool
   */
  public function actionUnsubscribePush()
  {
    $token = Yii::$app->request->post('token');
    $count = $this->findByToken($token)->count();
    $model = $this->findByToken($token)->andWhere(['user_id' => Yii::$app->user->id])->one();
    $result = $model ? $model->delete() : true;
    // Если записей больше 1, возвращаем false, чтобы не посылался запрос на отписку от уведомлений по текущему токену
    return ($count > 1)
      ? false
      : $result;
  }

  /**
   * @return array
   */
  public function actionUnsubscribeTelegram()
  {
    $result = Yii::$app->getModule('users')
      ->api('userTelegram', ['userId' =>  Yii::$app->user->id])
      ->unsetTelegramId();
    return AjaxResponse::set($result);
  }

  /**
   * @param $id
   * @return string
   */
  public function actionView($id)
  {

    $module = Yii::$app->getModule($id);

    $this->view->title = Yii::_t($module->name);

    /** @var \mcms\modmanager\models\Module $moduleApiResult */
    $moduleApiResult = Yii::$app->getModule('modmanager')
      ->api('moduleById', ['moduleId' => $id])
      ->getResult()
    ;

    if ($moduleApiResult === null) return $this->redirect($this->defaultAction);

    $searchModel = new NotificationSearch([
      'module_id' => $moduleApiResult->id
    ]);

    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    return $this->render('view', [
      'dataProvider' => $dataProvider,
      'module' => $module,
    ]);
  }

  private function handleForm(Notification $form, $events, $module)
  {
    $form->module_id = \mcms\modmanager\models\Module::findOne(['module_id' => $module['id']])->id;
    $this->performAjaxValidation($form);

    if (
      Yii::$app->request->isPost &&
      $form->load(Yii::$app->request->post()) &&
      $form->validate()
    ) {

      $form->save()
        ? $this->flashSuccess('app.common.saved_successfully')
        : $this->flashFail('app.common.save_failed')
      ;

      if ($form->isNew()) {
        return $this->redirect(['settings/update', 'id' => $form->id]);
      }
    }

    return $this->render('form', [
      'model' => $form,
      'replacementsDataProvider' => $form->getReplacementsDataProvider(),
      'events' => $events,
    ]);
  }

  /**
   * @param int $id Идентификатор модуля
   * @return string
   */
  public function actionAdd($id)
  {
    $module = ArrayHelper::getValue($this->_getModulesEventList(), $id);
    if ($module === NULL) {
      return $this->redirect([$this->defaultAction], []);
    }

    $this->view->title = Yii::_t('notifications.main.add_action');

    return $this->handleForm(
      new Notification([
        'scenario' => Notification::SCENARIO_CREATE
      ]),
      $this->getModuleEvents($module),
      $module
    );
  }

  /**
   * Performs ajax validation.
   * @param Model $model
   * @throws \yii\base\ExitException
   */
  protected function performAjaxValidation(Model $model)
  {
    if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      echo json_encode(ActiveForm::validate($model));
      Yii::$app->end();
    }
  }

  public function actionUpdate($id)
  {
    $notificationModel = $this->getNotificationModelById($id);
    if (!$notificationModel instanceof Notification) {
      return $notificationModel;
    }
    $notificationModel->scenario = Yii::$app->user->can('NotificationsSettingsEdit')
      ? Notification::SCENARIO_ADMIN_EDIT
      : Notification::SCENARIO_EDIT;

    $modulesEventList = $this->_getModulesEventList();
    $moduleIdentity = ArrayHelper::getValue(ArrayHelper::map($modulesEventList, 'db_id', 'id'),
      \mcms\modmanager\models\Module::findOne($notificationModel->module_id)->module_id
    );
    $module = ArrayHelper::getValue($modulesEventList, $moduleIdentity);

    $this->view->title = Yii::_t('notifications.main.update_action');

    return $this->handleForm($notificationModel, $this->getModuleEvents($module), $module);
  }

  private function getModuleEvents($module)
  {
    $moduleEvents = ArrayHelper::getValue($module, 'events', []);
    $events = [];
    if (count($moduleEvents)) foreach ($moduleEvents as $moduleEvent) {
      /** @var Event $eventInstance */
      $eventInstance = Yii::createObject($moduleEvent);
      $events[$moduleEvent] = $eventInstance->getEventName();
    }

    return $events;
  }

  public function actionDelete($id)
  {
    /** @var Notification $model */
    $model = Notification::findOne($id);
    return AjaxResponse::set($model->delete());
  }


  private function getNotificationModelById($id)
  {
    /** @var Notification $notificationModel */
    $notificationModel = Notification::findOne($id);
    if (!$notificationModel) {
      return $this->redirect(['settings/list']);
    }

    return $notificationModel;
  }

  /**
   * @param $id
   * @return Notification
   */
  private function getNotification($id)
  {
    $model = Notification::findOne($id);
    if ($model === null) return $this->redirect([$this->defaultAction]);

    return $model;
  }

  /**
   * @param $id
   * @param $enable
   * @return \yii\web\Response
   */
  private function _view($id, $enable)
  {
    $model = $this->getNotification($id);

    if ($enable) {
      $model->scenario = Module::SCENARIO_ENABLE;
      $model->setEnabled();
    } else {
      $model->scenario = Module::SCENARIO_DISABLE;
      $model->setDisabled();
    }

    return AjaxResponse::set($model->save());
  }

  public function actionDisable($id)
  {
    return $this->_view($id, false);
  }

  public function actionEnable($id)
  {
    return $this->_view($id, true);
  }

  /**
   * @param $token
   * @return ActiveQuery
   */
  protected function findByToken($token)
  {
    return UserPushToken::find()->andWhere(['token' => $token]);
  }

}