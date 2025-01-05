<?php

namespace mcms\partners\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\web\AjaxResponse;
use mcms\partners\models\NotificationForm;
use Yii;
use mcms\common\controller\SiteBaseController as BaseController;


/**
 * Class NotificationController
 * @package mcms\partners\controllers
 */
class NotificationController extends BaseController
{
  public $controllerTitle;
  public $categoryNoNav = true;

  /**
   * @inheritDoc
   */
  public function beforeAction($action)
  {
    $this->theme = 'basic';
    $this->controllerTitle = Yii::_t('partners.main.notification');


    return parent::beforeAction($action);
  }

  public function actionIndex()
  {
    $notificationForm = new NotificationForm();

    $notificationForm->load(array_merge(Yii::$app->request->queryParams, Yii::$app->request->post()));
    $notificationCategories = $notificationForm->getCategories();
    $modules = $notificationForm->getModules();

    $notificationsDataProvider = Yii::$app->getModule('notifications')
      ->api('getBrowserNotificationList', [
        'conditions' => ArrayHelper::merge($notificationForm->getAttributes(), [
          'user_id' => Yii::$app->user->id,
          'categoriesId' => array_keys($modules),
        ]),
       'pagination' => ['pageSize' => 10],
      ])
      ->getResult()
    ;

    $notificationTypes = [
      'all' => Yii::_t('partners.notifications.notify_all'),
      'news' => Yii::_t('partners.notifications.notify_news'),
      'important' => Yii::_t('partners.notifications.notify_important'),
    ];

    $notificationDate = [
      'today' => Yii::_t('partners.notifications.today'),
      'yesterday' => Yii::_t('partners.notifications.yesterday'),
      'week' => Yii::_t('partners.notifications.week'),
      'month' => Yii::_t('partners.notifications.month'),
    ];

    return $this->render('index', [
      'notificationsDataProvider' => $notificationsDataProvider,
      'notificationForm' => $notificationForm,
      'notificationCategories' => $notificationCategories,
      'notificationTypes' => $notificationTypes,
      'notificationDate' => $notificationDate,
      'modules' => $modules,
      'filterDatePeriods' => [
        'today' => [
          'from' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
        'yesterday' => [
          'from' => Yii::$app->formatter->asDate(strtotime('-1day'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(strtotime('-1day'), 'php:d.m.Y'),
        ],
        'week' => [
          'from' => Yii::$app->formatter->asDate(strtotime('-1week'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
        'month' => [
          'from' => Yii::$app->formatter->asDate(strtotime('-1month'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
      ],
    ]);
  }

  public function actionClear()
  {
    $clearResult = Yii::$app->getModule('notifications')
      ->api('setBrowserNotificationAsHidden', ['user_id' => Yii::$app->user->id])
      ->getResult()
    ;
    return AjaxResponse::set($clearResult)
      ;
  }

  public function actionReadAll()
  {
    return AjaxResponse::set(
      Yii::$app->getModule('notifications')->api('setBrowserNotificationAsViewed', [
        'user_id' => Yii::$app->user->id
      ])->getResult()
    );
  }


}
