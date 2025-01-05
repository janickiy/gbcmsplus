<?php

use admin\modules\credits\events\CreditApprovedEvent;
use admin\modules\credits\events\CreditDoneEvent;
use admin\modules\credits\events\CreditDeclinedEvent;
use admin\modules\credits\events\CreditExternalPaymentEvent;
use mcms\common\helpers\ArrayHelper;
use mcms\notifications\models\Notification;
use mcms\user\models\UserParam;
use console\components\Migration;

class m170823_052307_notifications extends Migration
{
  public function up()
  {
    // Добавить поле
    $this->addColumn('credits', 'user_id', $this->integer(10)->unsigned()->notNull()->after('external_id'));

    /** @var \admin\modules\credits\Module $creditsModule */
    $moduleEntity = Yii::$app->getModule('modmanager')
      ->api('moduleById', ['moduleId' => 'credits'])
      ->getResult();

    // Добавить модуль Кредиты в настройки уведомлений
    // Позаимствовано из модуля alerts
    $usersByRolesApi = Yii::$app->getModule('users')
      ->api('usersByRoles', ['reseller'])
      ->setResultTypeMap()
      ->setMapParams(['id', 'id'])
      ->getResult();

    $userParams = (new \yii\db\Query())->select('*')->from(UserParam::tableName())->where(['user_id' => $usersByRolesApi])->all();
    foreach ($userParams as $userParam) {
      $browserCategories = unserialize(ArrayHelper::getValue($userParam, 'notify_browser_categories', []));
      $emailCategories = unserialize(ArrayHelper::getValue($userParam, 'notify_email_categories', []));

      $browserCategories[] = $moduleEntity->id;
      $emailCategories[] = $moduleEntity->id;

      $this->update(UserParam::tableName(),
        [
          'notify_browser_categories' => serialize($browserCategories),
          'notify_email_categories' => serialize($emailCategories),
        ],
        [ 'user_id' => ArrayHelper::getValue($userParam, 'user_id'), ]
      );
    }


    // Добавить события
    $this->createNotification([
      'module_id' => $moduleEntity->id,
      'event' => CreditApprovedEvent::class,
      'type' => Notification::NOTIFICATION_TYPE_BROWSER,
      'header' => [
        'ru' => 'Ваш запрос кредита одобрен',
        'en' => 'Your request for credit was approved',
      ],
      'template' => [
        'ru' => 'Кредит #{credit.id} на сумму {credit.amount} одобрен',
        'en' => 'Credit #{credit.id} in the amount {credit.amount} approved',
      ],
      'use_owner' => true,
    ]);

    $this->createNotification([
      'module_id' => $moduleEntity->id,
      'event' => CreditDeclinedEvent::class,
      'type' => Notification::NOTIFICATION_TYPE_BROWSER,
      'header' => [
        'ru' => 'Ваш запрос кредита отклонен',
        'en' => 'Your request for credit was declined',
      ],
      'template' => [
        'ru' => 'Кредит #{credit.id} на сумму {credit.amount} отклонен.<br>
Причина: {credit.declineReason}',
        'en' => 'Credit #{credit.id} in the amount {credit.amount} declined.<br>
Reason: {credit.declineReason}',
      ],
      'use_owner' => true,
    ]);

    $this->createNotification([
      'module_id' => $moduleEntity->id,
      'event' => CreditDoneEvent::class,
      'type' => Notification::NOTIFICATION_TYPE_BROWSER,
      'header' => [
        'ru' => 'Кредит закрыт',
        'en' => 'Credit closed',
      ],
      'template' => [
        'ru' => 'Задолженность погашена. Кредит #{credit.id} на сумму {credit.amount} закрыт',
        'en' => 'Debt repaid. Credit #{credit.id} in the amount {credit.amount} closed',
      ],
      'use_owner' => true,
    ]);

    $this->createNotification([
      'module_id' => $moduleEntity->id,
      'event' => CreditExternalPaymentEvent::class,
      'type' => Notification::NOTIFICATION_TYPE_BROWSER,
      'header' => [
        'ru' => 'Бухгалтер добавил выплату по кредиту',
        'en' => 'Accounting added payment on the loan',
      ],
      'template' => [
        'ru' => 'Бухгалтер добавил выплату по кредиту #{credit.id} в размере {payment.amount}',
        'en' => 'Accounting added payment on a credit #{credit.id} in the amount of {payment.amount}',
      ],
      'use_owner' => true,
    ]);
  }

  public function down()
  {
    $this->dropColumn('credits', 'user_id');

    // Удалить модуль Кредиты из настроек уведомлений
    /** @var \admin\modules\credits\Module $creditsModule */
    $moduleEntity = Yii::$app->getModule('modmanager')
      ->api('moduleById', ['moduleId' => 'credits'])
      ->getResult();

    $usersByRolesApi = Yii::$app->getModule('users')
      ->api('usersByRoles', ['reseller'])
      ->setResultTypeMap()
      ->setMapParams(['id', 'id'])
      ->getResult();

    $userParams = \mcms\user\models\UserParam::find()->where(['user_id' => $usersByRolesApi])->all();
    foreach ($userParams as $userParam) {
      $browserCategories = $userParam->getNotifyBrowserCategories();
      $emailCategories = $userParam->getNotifyEmailCategories();

      $browserCategories = array_filter($browserCategories, function ($value) use ($moduleEntity) {
        return $value != $moduleEntity->id;
      });
      $emailCategories = array_filter($emailCategories, function ($value) use ($moduleEntity) {
        return $value != $moduleEntity->id;
      });

      $userParam->notify_browser_categories = $browserCategories;
      $userParam->notify_email_categories = $emailCategories;
      $userParam->save();
    }

    $this->delete('notifications', ['event' => [
      CreditApprovedEvent::class,
      CreditDeclinedEvent::class,
      CreditDoneEvent::class,
      CreditExternalPaymentEvent::class,
    ]]);
  }

  /**
   * Сделано аналогично @see \m160306_181406_update_notifications
   * @param array $notification
   */
  private function createNotification(array $notification)
  {
    $model = new Notification;
    $model->module_id = ArrayHelper::getValue($notification, 'module_id');
    $model->event = ArrayHelper::getValue($notification, 'event');
    $model->notification_type = ArrayHelper::getValue($notification, 'type');
    $model->header = ArrayHelper::getValue($notification, 'header');
    $model->template = ArrayHelper::getValue($notification, 'template');
    $model->use_owner = ArrayHelper::getValue($notification, 'use_owner', false);

    $roles = ArrayHelper::getValue($notification, 'roles', []);
    if ($roles) $model->setRoles($roles);

    $model->from = ArrayHelper::getValue($notification, 'from');

    $model->save(false);
  }
}
