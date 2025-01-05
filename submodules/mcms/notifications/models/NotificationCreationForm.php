<?php

namespace mcms\notifications\models;

use froala\froalaeditor\FroalaEditorWidget;
use mcms\common\helpers\ArrayHelper;

use kartik\builder\Form;
use mcms\common\helpers\Html;
use mcms\mcms\common\widget\RgkTinyMce;
use mcms\notifications\components\event\NotificationCreationFormEvent;
use yii\base\Exception;
use yii\helpers\Url;
use Yii;

/**
 * Модель для создания уведомлений через админку.
 * Используется сценарий Notification::SCENARIO_MANUAL_CREATION
 * @package mcms\notifications\models
 */
class NotificationCreationForm extends Notification
{
  const PERMISSION_CAN_NOTIFY_ROLE_PREFIX = 'NotificationsCanNotify';

  public $roles;
  public $header;
  public $from;
  public $template;
  public $fromModule;
  public $notificationType;
  public $isImportant = false;
  public $isNews = false;

  public function __construct($config = [])
  {
    parent::__construct($config);

    // TRICKY: пока оставляем для рассылки только емейл и браузерные уведомления
    $notificationTypeList = [
      self::NOTIFICATION_TYPE_BROWSER => ArrayHelper::getValue(static::$notificationTypeList, self::NOTIFICATION_TYPE_BROWSER),
      self::NOTIFICATION_TYPE_EMAIL => ArrayHelper::getValue(static::$notificationTypeList, self::NOTIFICATION_TYPE_EMAIL),
    ];

    $this->notificationTypes = ArrayHelper::map(
      $notificationTypeList, 'id', function ($item) {
      return Yii::_t(ArrayHelper::getValue($item, 'title'));
    });
  }

  public function getMultilangAttributes()
  {
    return ['header', 'template'];
  }

  public function getFormAttributes()
  {
    return [
      'header' => [
        'type' => Form::INPUT_TEXT,
        'label' => Yii::_t('labels.notification_creation_header')
      ],
      'template' => [
          'type' => Form::INPUT_WIDGET,
          'widgetClass' => RgkTinyMce::class,
          'options' => [
            'language' => 'en',
            'clientOptions' => [
              'height' => 400,
              'plugins' => [
                'image code lists link hr fullscreen',
              ],
              'menubar' => false,
              'branding' => false,

              'toolbar' => 'code formatselect bold italic strikethrough bullist numlist outdent indent image link align hr fullscreen',
              'images_upload_url' => Html::hasUrlAccess(['/notifications/notifications/image-upload/'])
                ? Url::toRoute(['notifications/image-upload/'])
                : null,
              'image_dimensions' => false,
              'image_description' => false,
              'image_class_list' => [
                ['title' => 'Full width', 'value' => 'img-full-width'],
                ['title' => 'Common', 'value' => 'img-common'],
              ],

              'relative_urls' => false,
              'remove_script_host' => false,
              'convert_urls' => true,
              'content_style' => '.img-full-width { width: 100%; margin: 0; }',
            ]
          ],
      ],
    ];
  }

  public function rules()
  {
    // TRICKY Так как fromModule обязателен, при создании уведомлений вручную,
    // автоматически указывается fromModule=2 (уведомления), хоть он и не имеет событий
    return [
      [['isTest', 'isReplace'], 'boolean'],
      [['roles', 'fromModule', 'notificationType'], 'required'],
      [['roles'], 'in', 'range' => $this->getRoles(), 'allowArray' => true],
      ['fromModule', 'compare', 'compareValue' => 0, 'operator' => '>'],
      [['isImportant', 'isNews'], 'default', 'value' => false, 'skipOnEmpty' => false],
      [['header'], 'validateArrayRequired'],
      [['template'], 'validateArrayRequired'],
      [['from'], 'required', 'when' => function ($model) {
        return is_array($this->notificationType) && in_array(Notification::NOTIFICATION_TYPE_EMAIL, $model->notificationType);
      }, 'whenClient' => "function (attribute, value) {
            var values = $('#notificationcreationform-notificationtype').val();
            var result = false;
            $(values).each(function (key, value) {
              if(value == '" . self::NOTIFICATION_TYPE_EMAIL . "') {
                result = true;
              }
            });
            return result;
      }"],
    ];
  }

  public function fromModules()
  {
    return [0 => Yii::_t('app.common.choose')] + Yii::$app->getModule('modmanager')
        ->api('modulesWithEvents', ['useDbId', 'translateName'])
        ->getResult();
  }

  /**
   * Получить все доступные роли
   * @return array
   * @throws \yii\base\InvalidConfigException
   */
  public function getRoles()
  {
    return array_filter(
      Yii::$app->getModule('users')
        ->api('roles', ['removeGuest'])
        ->getResult(),
      function ($role) {
        return Yii::$app->user->can(self::PERMISSION_CAN_NOTIFY_ROLE_PREFIX . ucfirst($role));
      }
    );
  }

  /**
   * @param $notificationType
   * @throws Exception
   *
   * @see NotificationCreationForm::isTest
   */
  private function send($notificationType)
  {
    $notificationsModule = Yii::$app->getModule('modmanager')->api('moduleById', ['moduleId' => 'users'])->getResult();
    if (!$notificationsModule) throw new Exception('Модуль users не найден');

    $notificationModel = new Notification;
    $notificationModel->module_id = $notificationsModule->id;
    $notificationModel->is_news = true;
    $notificationModel->use_owner = $this->isTest;
    $notificationModel->event = NotificationCreationFormEvent::class;
    $notificationModel->from = ['ru' => '{noreply_email}', 'en' => '{noreply_email}'];
    $notificationModel->header = $this->header;
    $notificationModel->template = $this->template;
    $notificationModel->is_important = $this->isImportant;
    $notificationModel->is_system = $this->is_system;
    $notificationModel->roles = $this->isTest ? [] : $this->roles;
    $notificationModel->notification_type = $notificationType;
    $notificationModel->isTest = $this->isTest;
    $notificationModel->isReplace = $this->isReplace;
    $notificationModel->isForceSend = true;

    Yii::$app->getModule('notifications')->api('sendNotification', [
      'notificationModel' => $notificationModel,
      'event' => new NotificationCreationFormEvent($this->isTest ? Yii::$app->user->identity : null)
    ])->send();
  }

  public function getReplacementsDataProvider()
  {
    $this->event = NotificationCreationFormEvent::class;
    return parent::getReplacementsDataProvider();
  }

  /**
   * Создать рассылку.
   * Под ивентом подразумевается рассылка
   * @return bool
   *
   * @see NotificationCreationForm::isTest
   */
  public function triggerEvent()
  {
    $types = $this->isTest ? [static::NOTIFICATION_TYPE_EMAIL] : $this->notificationType;

    array_map(function ($notificationType) {
      $this->send($notificationType);
    }, $types);
  }
}