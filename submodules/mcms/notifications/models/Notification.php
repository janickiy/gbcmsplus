<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 8/25/15
 * Time: 4:37 PM
 */

namespace mcms\notifications\models;

use mcms\notifications\models\queries\NotificationsActiveQuery;
use mcms\user\components\api\Roles;
use Yii;
use yii\helpers\Url;
use yii\db\ActiveQuery;
use kartik\builder\Form;
use mcms\user\models\Role;
use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;
use yii\validators\EmailValidator;
use mcms\modmanager\models\Module;
use yii\behaviors\TimestampBehavior;
use mcms\common\traits\model\Disabled;
use mcms\common\multilang\MultiLangModel;
use mcms\common\traits\model\FormAttributes;

/**
 * Class Notification
 * @package mcms\notifications\models
 *
 * @property int $id
 * @property int $module_id
 * @property array $roles
 * @property int $use_owner
 * @property int $is_disabled
 * @property string $from
 * @property string $header
 * @property string $template
 * @property string $notification_type
 * @property string $is_important
 * @property string $is_system
 * @property string $is_news
 * @property string $event
 * @property string $emails
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $emails_language
 */
class Notification extends MultiLangModel
{
  use FormAttributes, Disabled;

  const SCENARIO_CREATE = 'create';
  const SCENARIO_EDIT = 'edit';
  const SCENARIO_ADMIN_EDIT = 'admin_edit';
  const SCENARIO_EDIT_TEMPLATE = 'editTemplate';
  const SCENARIO_VIEW_TEMPLATE = 'viewTemplate';
  const SCENARIO_REPLACEMENT = 'replacement';

  const NOTIFICATION_TYPE_BROWSER = 1;
  const NOTIFICATION_TYPE_EMAIL = 2;
//  const NOTIFICATION_TYPE_SMS = 3; не стал удалять строку, для истории
  const NOTIFICATION_TYPE_TELEGRAM = 4;
  const NOTIFICATION_TYPE_PUSH = 5;

  const DEFAULT_LANG = 'ru';

  public $notificationTypes = [];
  public $formAttributes = [];

  /**
   * @var bool Тестовая рассылка
   * @see NotificationCreationForm
   * TRICKY Для использования нужно передать этот параметр в NotificationCreationForm.
   * На прямую в Notification указать нельзя, иначе будут выполнены не все действия соответсвующие этому параметру
   * (по крайней мере такой вариант не тестировался)
   */
  public $isTest = false;

  /**
   * @var bool Заменить последнее уведомление по возможности.
   * TRICKY Для использования нужно передать этот параметр в NotificationCreationForm.
   * Передача параметра на прямую в Notification не тестировалась, но вероятнее всего отработает так как надо
   */
  public $isReplace = false;

  /**
   * @var bool Принудительно отправлять, даже если замьючено.
   * Необходимо при отправке уведомлений вручную из админки, проверяется здесь:
   * @see \mcms\notifications\components\event\driver\AbstractDriver::checkIgnore()
   */
  public $isForceSend = false;

  /**
   * @var string|array|null Список ролей. Кроме ролей может содержать в себе значение owner
   * TRICKY Это костыльная переменная, с ней надо работать как при разминировании
   * Передача строки
   * - если строка пустая, то будут отвязаны все роли и use_owner установится как false
   * - если строка не пустая (например 'owner'), то она будет преобразована в PHP-массив (например ['owner'])
   * Передача массива
   * - если в массиве есть owner, все роли будут отвязаны и будет установлен use_owner true
   * - если в массиве нет owner и переданы роли, то будет установлен use_owner false и привязаны соответствующие роли
   * - если массив пустой, будут привязаны все возможные роли
   * Передача null
   * - ничего не делать
   */
  private $_roles;

  private $_isNew;

  /**
   * @var string см. getter/setter
   */
  private $_types;
  /**
   * @var string см. getter/setter
   */
  private $_ids;
  /**
   * @var string см. getter/setter
   */
  private $_enabled;
  /**
   * @var null см. self::getGroupedNotificationsInfo()
   */
  private $groupedNotificationsCache = null;
  /**
   * @var array кеширование id нотификаций полученным по event и type
   */
  private static $_cachedIds = [];

  static $notificationTypeList = [
    self::NOTIFICATION_TYPE_BROWSER => [
      'id' => self::NOTIFICATION_TYPE_BROWSER,
      'name' => 'browser',
      'title' => 'notifications.notification_types.browser'
    ],
    self::NOTIFICATION_TYPE_EMAIL => [
      'id' => self::NOTIFICATION_TYPE_EMAIL,
      'name' => 'email',
      'title' => 'notifications.notification_types.email'
    ],
    self::NOTIFICATION_TYPE_TELEGRAM => [
      'id' => self::NOTIFICATION_TYPE_TELEGRAM,
      'name' => 'telegram',
      'title' => 'notifications.notification_types.telegram'
    ],
    self::NOTIFICATION_TYPE_PUSH => [
      'id' => self::NOTIFICATION_TYPE_PUSH,
      'name' => 'push',
      'title' => 'notifications.notification_types.push'
    ],
  ];

  /**
   * Notification constructor.
   * @param array $config
   */
  public function __construct($config = [])
  {
    $this->notificationTypes = ArrayHelper::map(
      static::$notificationTypeList, 'id', function ($item) {
      return Yii::_t(ArrayHelper::getValue($item, 'title'));
    });

    $this->formAttributes = [
      'from' => [
        'type' => Form::INPUT_TEXT,
        'label' => Yii::_t('notifications.labels.notification_creation_from'),
      ],
      'header' => [
        'type' => Form::INPUT_TEXT,
        'label' => Yii::_t('notifications.labels.notification_creation_header'),
      ],
      'template' => [
        'type' => Form::INPUT_WIDGET,
        'label' => Yii::_t('notifications.labels.notification_creation_template'),
        'widgetClass' => 'vova07\imperavi\Widget',
        'options' => [
          'settings' => [
            'minHeight' => '300px',
            'imageUpload' => Url::toRoute(['/' . Yii::$app->getModule('notifications')->id . '/notifications/image-upload/']),
            'plugins' => ['imagemanager', 'fullscreen'],
            'paragraphize' => false,
          ]
        ],
      ],
    ];

    $this->_isNew = !$this->id;

    parent::__construct($config);
  }

  /**
   * Метод используется если модель получена MyNotificationSearch
   * Получаем сгруппированные нотификации
   * [
   * 'id' => int|null,
   * 'enabled' => bool,
   * 'name' => browser|email|telegram,
   * 'title' => string,
   * ];
   * @return array
   */
  public function getGroupedNotificationsInfo($selectType = null)
  {
    if ($this->groupedNotificationsCache === null) {
      $types = [];
      for ($i = 0; $i < count($this->ids); $i++) {
        $types[$this->types[$i]] = [
          'id' => $this->ids[$i],
          'enabled' => $this->enabled[$i],
        ];
      }
      $this->groupedNotificationsCache = [];
      foreach (self::$notificationTypeList as $type => $item) {
        $this->groupedNotificationsCache[$type] = [
          'id' => isset($types[$type]) ? $types[$type]['id'] : null,
          'enabled' => isset($types[$type]) ? $types[$type]['enabled'] : false,
          'name' => $item['name'],
          'title' => Yii::_t($item['title']),
        ];
      }
    }
    if ($selectType) {
      return $this->groupedNotificationsCache[$selectType];
    }
    return $this->groupedNotificationsCache;
  }

  /**
   * Получить массив типов нотификаций сгруппированных по полю event (см GroupedNotificationsActiveQuery)
   * @return array
   */
  public function getTypes()
  {
    return explode(',', $this->_types);
  }

  /**
   * Сеттер типов нотификаций сгруппированных типов нотификаций по полю event (см GroupedNotificationsActiveQuery)
   * @param $value
   */
  public function setTypes($value)
  {
    $this->_types = is_array($value) ? implode(',', $value) : $value;
  }

  /**
   * Получить массив id нотификаций сгруппированных по полю event (см GroupedNotificationsActiveQuery)
   * @return array
   */
  public function getIds()
  {
    return explode(',', $this->_ids);
  }

  /**
   * Сеттер id нотификаций сгруппированных по полю event (см GroupedNotificationsActiveQuery)
   * @param $value
   */
  public function setIds($value)
  {
    $this->_ids = is_array($value) ? implode(',', $value) : $value;
  }

  /**
   * Получить массив сгруппированных по полю event значений доступности нотификаций
   * @return array
   */
  public function getEnabled()
  {
    return explode(',', $this->_enabled);
  }

  /**
   * Сеттер доступности нотификаций сгруппированных по полю event (см GroupedNotificationsActiveQuery)
   * @param $value
   */
  public function setEnabled($value)
  {
    $this->_enabled = is_array($value) ? implode(',', $value) : $value;
  }

  /**
   * @return array
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'module_id' => Yii::_t('notifications.main.module_id'),
      'fromModule' => Yii::_t('notifications.main.module_name'),
      'event' => Yii::_t('notifications.main.event'),
      'roles' => Yii::_t('notifications.main.role'),
      'emails' => Yii::_t('notifications.main.emails'),
      'emails_language' => Yii::_t('notifications.main.emails_language'),
      'from' => Yii::_t('notifications.labels.notification_creation_from'),
      'header' => Yii::_t('notifications.labels.notification_creation_header'),
      'template' => Yii::_t('notifications.labels.notification_creation_template'),
      'notificationType' => Yii::_t('notifications.labels.notification_creation_notificationType'),
      'notification_type' => Yii::_t('notifications.labels.notification_creation_notificationType'),
      'is_important' => Yii::_t('labels.notification_creation_isImportant'),
      'isImportant' => Yii::_t('labels.notification_creation_isImportant'),
      'is_news' => Yii::_t('labels.notification_creation_isNews'),
      'isNews' => Yii::_t('labels.notification_creation_isNews'),
      'is_disabled' => Yii::_t('labels.notification_creation_isDisabled'),
      'is_system' => Yii::_t('labels.notification_creation_isSystem'),
      'isReplace' => Yii::_t('labels.notification_creation_isReplace'),
    ];
  }

  public function getMultilangAttributes()
  {
    if (in_array($this->getScenario(), [
      \mcms\notifications\Module::SCENARIO_DISABLE,
      \mcms\notifications\Module::SCENARIO_ENABLE,
      self::SCENARIO_CREATE,
      self::SCENARIO_REPLACEMENT,
    ])) return [];

    return ['from', 'header', 'template'];
  }

  /**
   * @inheritDoc
   */
  public static function tableName()
  {
    return '{{%notifications}}';
  }

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_CREATE => [
        'module_id',
        'event',
        'roles',
        'emails',
        'emails_language',
        'use_owner',
        'is_important',
        'is_news',
        'notification_type',
        'is_disabled',
        'is_system',
      ],
      self::SCENARIO_ADMIN_EDIT => [
        'module_id',
        'event',
        'roles',
        'emails',
        'emails_language',
        'use_owner',
        'is_important',
        'is_news',
        'template',
        'header',
        'notification_type',
        'from',
        'is_disabled',
        'is_system',
      ],
      self::SCENARIO_EDIT => [
        'module_id',
        'event',
        'emails',
        'emails_language',
        'use_owner',
        'is_important',
        'is_news',
        'template',
        'header',
        'notification_type',
        'from',
        'is_disabled',
        'is_system',
      ],
      \mcms\notifications\Module::SCENARIO_DISABLE => ['is_disabled'],
      \mcms\notifications\Module::SCENARIO_ENABLE => ['is_disabled'],
      self::SCENARIO_EDIT_TEMPLATE => [
        'template',
        'header',
        'type',
        'notification_type',
        'from'
      ],
      self::SCENARIO_VIEW_TEMPLATE => [
        'template',
        'header',
        'type',
        'notification_type',
        'from'
      ],
      self::SCENARIO_REPLACEMENT => [
        'from',
        'header',
        'template',
      ]
    ]);
  }

  public function rules()
  {
    return array_merge(
      parent::rules(), [
        [['isReplace'], 'boolean'],
        ['emails_language', 'string'],
        [['header', 'template', 'event', 'notification_type'], 'required'],
        ['from', 'required', 'when' => function (Notification $model) {
          return $model->getNamedNotificationType() == $model::NOTIFICATION_TYPE_EMAIL;
        }],
        [['from', 'header', 'template'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
        [['from', 'header', 'template'], 'default', 'value' => ''],
        [['is_important', 'is_system', 'is_news', 'use_owner', 'is_disabled'], 'boolean'],
      ]
    );
  }

  /**
   * получаем актив квери для нотификаций
   * @return NotificationsActiveQuery
   */
  public static function find()
  {
    return new NotificationsActiveQuery(get_called_class());
  }

  /**
   * Получить все доступные роли
   * TRICKY Роли дополнены записью owner независимо от флага use_owner в уведомлении
   * @return array
   * @throws \yii\base\InvalidConfigException
   */
  public function getAllRoles()
  {
    return Yii::$app->getModule('users')
      ->api('roles', ['withOwner' => true, 'removeGuest'])
      ->getResult();
  }

  /**
   * @return array|ActiveQuery
   * @see getRolesAsArray()
   */
  public function getRoles()
  {
    return $this->isNewRecord
      ? $this->_roles
      : $this->hasMany(Role::class, ['name' => 'auth_item_name'])
        ->viaTable('notifications_auth_item', ['notification_id' => 'id']);
  }

  /**
   * Список ролей в виде массива
   * @return string[]
   */
  public function getRolesAsArray()
  {
    $roles = $this->getRoles();
    if (is_array($roles)) return $roles;
    if ($roles instanceof ActiveQuery) return $roles->select('name')->column();

    return [];
  }

  /**
   * Список ролей для отображения.
   * TRICKY При отправке уведомлений этот метод не используется. Он сделан только для отображения.
   * При используется проверка на use_owner, если use_owner == true, то уведомление отправляется owner'у, иначе отправляется ролям
   * @return string[] Если свойство use_owner == true, то метод вернет ['owner'], иначе вернет список ролей уведомления
   */
  public function getRolesToShow()
  {
    /** @var \mcms\user\Module $module */
    /** @var Roles $rolesApi */
    $module = Yii::$app->getModule('users');
    $rolesApi = $module->api('roles');

    return $this->use_owner ? [$rolesApi->getOwnRole()] : $this->getRolesAsArray();
  }

  /**
   * @param array $roles
   */
  public function setRoles($roles)
  {
    $this->_roles = $roles;
  }

  /**
   * @inheritdoc
   */
  public function beforeSave($insert)
  {
    /** @var Roles $rolesApi */
    /** @var \mcms\user\Module $usersModule */
    $usersModule = Yii::$app->getModule('users');

    // Если указаны роли
    if ($this->_roles !== NULL) {
      // Если вместо списка полей пустая строка, значит значение пришло из формы управления уведомлением и ничего не выбрано
      if ($this->_roles === '') $this->_roles = [];
      if (!is_array($this->_roles)) $this->_roles = [$this->_roles];

      // Если в модель передан use_owner true (именно передан сейчас, а не было установлено когда-то),
      // или если в списке ролей есть owner, то принудительно убираем роли и устаналиваем use_owner true
      // Иначе use_owner ставим false и в afterSave привязываем роли
      $rolesApi = $usersModule->api('roles');
      if (in_array($rolesApi->getOwnRole(), $this->_roles)
        || ($this->isAttributeChanged('use_owner') && $this->use_owner)) {
        $this->_roles = [];
        $this->use_owner = 1;
      } else {
        $this->use_owner = 0;
      }
    }

    return parent::beforeSave($insert);
  }

  /**
   * @inheritdoc
   */
  public function afterSave($insert, $changedAttributes)
  {
    if ($this->_roles !== NULL) {
      // Если roles === false, удаляем все роли и всё
      if (!$insert) {
        $this->unlinkAll('roles', true);
      }

      if ($this->_roles !== []) {
        $roleList = Role::findAll($this->_roles);
        foreach ($roleList as $role) {
          $this->link('roles', $role);
        }
      }
    }

    parent::afterSave($insert, $changedAttributes);
  }


  public function getModule()
  {
    return $this->hasOne(Module::class, ['id' => 'module_id']);
  }

  /**
   * @return boolean
   */
  public function isNew()
  {
    return $this->_isNew;
  }

  /**
   * @return bool
   */
  public function isOwner()
  {
    return !!$this->getAttribute('use_owner');
  }

  /**
   * @return mixed
   */
  public function getNotificationType()
  {
    return $this->getAttribute('notification_type');
  }

  public function getNamedNotificationType()
  {
    return ArrayHelper::getValue($this->notificationTypes, $this->getNotificationType());
  }

  public function afterFind()
  {
    parent::afterFind();
  }

  public function beforeValidate()
  {
    $this->emails = array_map(function ($email) {
      return trim($email);
    }, explode(',', $this->emails));

    $emailValidator = new EmailValidator();
    $this->emails = array_filter($this->emails, function ($email) use ($emailValidator) {
      return $emailValidator->validate($email);
    });

    $this->emails = implode(',', $this->emails);
    return parent::beforeValidate();
  }

  public function getReplacementsDataProvider()
  {
    if (!$this->event) {
      return new ArrayDataProvider([
        'allModels' => [],
      ]);
    }
    $eventInstance = Yii::createObject($this->event);
    $replacements = $eventInstance->getReplacementsHelp();
    $models = [];

    foreach ($replacements as $replacement => $help) {
      $models[] = [
        'key' => $replacement,
        'help' => $help
      ];
    }

    return new ArrayDataProvider([
      'pagination' => [
        'pageSize' => count($replacements),
      ],
      'allModels' => $models,
    ]);
  }

  /**
   * Получить id нотификации  по свойству event и типу
   * Нужен потому что при формировании рассылок по ролям теряются оригинальные id нотификаций
   * @param Notification $notification
   * @return mixed
   */
  public static function fetchId(Notification $notification)
  {
    $className = $notification->event;

    if (empty(self::$_cachedIds[$className][$notification->notification_type])) {
      self::$_cachedIds[$className][$notification->notification_type] = Notification::find()->where([
        'event' => $className,
        'notification_type' => $notification->notification_type
      ])->one()->id;
    }

    return self::$_cachedIds[$className][$notification->notification_type];
  }
}