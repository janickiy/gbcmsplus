<?php

namespace mcms\promo\models;

use mcms\common\traits\model\Disabled;
use mcms\common\helpers\ArrayHelper;
use mcms\common\validators\AlphanumericalValidator;
use mcms\promo\components\events\ProviderCreated;
use mcms\promo\components\events\ProviderRedirected;
use mcms\promo\components\events\ProviderUpdated;
use mcms\user\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use mcms\common\helpers\Link;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%providers}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $url
 * @property integer $status
 * @property string $settings
 * @property string $handler_class_name
 * @property integer $redirect_to
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $is_rgk
 * @property string $secret_key
 *
 * @property Landing[] $landings
 * @property User $createdBy
 * @property Provider $redirectTo
 * @property Provider[] $providers
 */
class Provider extends \yii\db\ActiveRecord
{
  use Disabled;

  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;

  const SCENARIO_REDIRECT = 'redirect';
  const SCENARIO_CREATE_EXTERNAL = 'create_external';
  const SCENARIO_UPDATE_EXTERNAL = 'update_external';
  const SCENARIO_CREATE = 'create';
  const SCENARIO_UPDATE = 'update';

  // TRICKY Хэндлеры должны быть описаны в @see getHandlers()
  /** @const string Название класса-обработчика Mobleaders */
  const HANDLER_ML = 'Mobleaders';
  /** @const string Название класса-обработчика КП */
  const HANDLER_KP = 'KP';
  /** @const string Название класса дефолтного обработчика */
  const HANDLER_DEFAULT = 'Default';

  const MOBLEADERS = 'mobleaders';

  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%providers}}';
  }

  /**
   * @param null|int $status
   * @return array
   */
  public static function getDropdownItems($status = null)
  {
    return static::find()
      ->select('name')
      ->andFilterWhere(['status' => $status])
      ->orderBy('name')
      ->indexBy('id')
      ->column();
  }

  /**
   * Фильтрация полей
   * @see \rgk\utils\helpers\FilterHelper
   */
  public function filterRules()
  {
    return [
      'settings' => false,
    ];
  }
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['handler_class_name', 'in', 'range' => ArrayHelper::getColumn(static::getHandlers(), 'code')],
      ['created_by', 'default', 'value' => isset(Yii::$app->user) ? Yii::$app->user->id : null],
      [['name', 'code', 'url', 'created_by', 'status'], 'required'],
      [['status', 'redirect_to', 'created_by', 'is_rgk'], 'integer'],
      [['name'], 'string', 'max' => 100],
      [['code'], 'string', 'max' => 10],
      [['code'], AlphanumericalValidator::class],
      [
        'code',
        function ($attribute, $params) {
          $firstLetters = substr(strtolower($this->$attribute), 0, 2);
          if ($firstLetters === 'kp') {
            $this->addError($attribute, Yii::_t('promo.providers.wrong-code'));
          }

        },
        'on' => [self::SCENARIO_CREATE_EXTERNAL, self::SCENARIO_UPDATE_EXTERNAL]
      ],
      [['url'], 'string', 'max' => 255],
      ['url', 'url'],
      [['handler_class_name', 'settings'], 'string'],

      ['redirect_to', 'checkRedirectToActive', 'on' => self::SCENARIO_REDIRECT],

      [['code'], 'unique'],
      [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
      [['redirect_to'], 'exist', 'skipOnError' => true, 'targetClass' => Provider::class, 'targetAttribute' => ['redirect_to' => 'id']],
    ];
  }

  public function checkRedirectToActive($attribute, $params)
  {
    if ($this->redirect_to && $this->redirectTo->status != Provider::STATUS_ACTIVE) {
      $this->addError($attribute, Yii::_t('promo.providers.redirect-to-active-only'));
    }
  }

  public function scenarios()
  {
    $attributes = $this->getAttributes();
    $scenarioCreateAttributes = array_keys($attributes);
    unset($attributes['is_rgk']);
    $scenarioCreateExternalAttributes = array_keys($attributes);

    $scenarios = parent::scenarios();
    $scenarios[self::SCENARIO_REDIRECT] = ['redirect_to'];
    $scenarios[self::SCENARIO_CREATE] = $scenarioCreateAttributes;
    $scenarios[self::SCENARIO_UPDATE] = $scenarioCreateAttributes;
    $scenarios[self::SCENARIO_CREATE_EXTERNAL] = $scenarioCreateExternalAttributes;
    $scenarios[self::SCENARIO_UPDATE_EXTERNAL] = $scenarioCreateExternalAttributes;
    return $scenarios;
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'name' => Yii::_t('promo.providers.attribute-name'),
      'code' => Yii::_t('promo.providers.attribute-code'),
      'url' => Yii::_t('promo.providers.attribute-url'),
      'status' => Yii::_t('promo.providers.attribute-status'),
      'settings' => Yii::_t('promo.providers.attribute-settings'),
      'handler_class_name' => Yii::_t('promo.providers.attribute-handler_class_name'),
      'redirect_to' => Yii::_t('promo.providers.attribute-redirect_to'),
      'created_by' => Yii::_t('promo.providers.attribute-created_by'),
      'created_at' => Yii::_t('promo.providers.attribute-created_at'),
      'updated_at' => Yii::_t('promo.providers.attribute-updated_at'),
      'secret_key' => Yii::_t('promo.provider_settings.attribute-secretKey'),
    ];
  }


  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLanding()
  {
    return $this->hasMany(Landing::class, ['provider_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCreatedBy()
  {
    return $this->hasOne(User::class, ['id' => 'created_by']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getRedirectTo()
  {
    return $this->hasOne(Provider::class, ['id' => 'redirect_to']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getProvider()
  {
    return $this->hasMany(Provider::class, ['redirect_to' => 'id']);
  }

  public function getStatuses($status = null)
  {
    $list = [
      self::STATUS_INACTIVE => Yii::_t('promo.providers.status-inactive'),
      self::STATUS_ACTIVE => Yii::_t('promo.providers.status-active')
    ];
    return isset($status) ? ArrayHelper::getValue($list, $status, null) : $list;
  }


  public function getCurrentStatusName()
  {
    return $this->getStatuses($this->status);
  }


  public function getReplacements()
  {
    /** @var User $createdBy */
    $createdBy = $this->getCreatedBy()->one();

    /** @var Provider $redirect */
    $redirect = $this->getRedirectTo()->one();
    return [
      'id' => [
        'value' => $this->isNewRecord ? null : $this->id,
        'help' => [
          'label' => Yii::_t('promo.replacements.provider_id')
        ]
      ],
      'name' => [
        'value' => $this->isNewRecord ? null : $this->name,
        'help' => [
          'label' => Yii::_t('promo.replacements.provider_name')
        ]
      ],
      'status' => [
        'value' => $this->isNewRecord ? null : $this->status,
        'help' => [
          'label' => Yii::_t('promo.replacements.provider_status')
        ]
      ],
      'code' => [
        'value' => $this->isNewRecord ? null : $this->code,
        'help' => [
          'label' => Yii::_t('promo.replacements.provider_code')
        ]
      ],
      'url' => [
        'value' => $this->isNewRecord ? null : $this->url,
        'help' => [
          'label' => Yii::_t('promo.replacements.provider_url')
        ]
      ],
      'createdBy' => [
        'value' => $this->isNewRecord ? null : $createdBy->getReplacements(),
        'help' => [
          'label' => Yii::_t('promo.replacements.provider_createdBy'),
          'class' => Yii::$app->user->identityClass
        ]
      ],
    ];
  }

  public function afterSave($insert, $changedAttributes)
  {
    if ($insert) {
      (new ProviderCreated($this))->trigger();
    } else if ($this->scenario === self::SCENARIO_REDIRECT) {
      (new ProviderRedirected($this))->trigger();
    } else {
      (new ProviderUpdated($this))->trigger();
    }

    parent::afterSave($insert, $changedAttributes);
  }

  /**
   * Если создаем провайдер RGK, выставляем соответствующий флаг
   * @return bool
   */
  public function beforeValidate()
  {
    if ($this->getScenario() === self::SCENARIO_CREATE) {
      $this->is_rgk = 1;
    }

    if ($this->getScenario() === self::SCENARIO_CREATE_EXTERNAL) {
      $this->handler_class_name = self::HANDLER_DEFAULT;
      $this->secret_key = Yii::$app->getSecurity()->generateRandomString();
    }

    return parent::beforeValidate();
  }

  public function getRedirectToDropDownList()
  {
    $query = self::find()
      ->where('id <> :current_id', [':current_id' => $this->id])
      ->andWhere(['status' => self::STATUS_ACTIVE])
      ->orderBy('name');

    if ($this->redirect_to) {
      // На случай если редактируем провайдер, у которого уже настроен редирект на неактивный провайдер.
      // Иначе в дропдауне будет показан просто id провайдера
      $query->orWhere(['id' => $this->redirect_to]);
    }

    return ArrayHelper::map($query->each(), 'id', 'name');
  }

  /**
   * @return string
   */
  public function getRedirectToLink()
  {
    return $this->redirect_to ? Link::get('/promo/providers/view', ['id' => $this->redirect_to], ['data-pjax' => 0], $this->redirectTo->name) : null;
  }

  /**
   * @return string
   */
  public function getViewLink()
  {
    return Link::get('/promo/providers/view', ['id' => $this->id], ['data-pjax' => 0], $this->getStringInfo(), false);
  }

  /**
   * @return string
   */
  public function getStringInfo()
  {
    return Yii::$app->formatter->asText($this->name);
  }

  /**
   * @return bool
   */
  public function isDisabled()
  {
    return $this->status !== self::STATUS_ACTIVE;
  }

  /**
   * @return bool
   */
  public function isEnabled()
  {
    return $this->status === self::STATUS_ACTIVE;
  }

  /**
   * @return $this
   */
  public function setEnabled()
  {
    $this->status = self::STATUS_ACTIVE;
    return $this;
  }

  /**
   * @return $this
   */
  public function setDisabled()
  {
    $this->status = self::STATUS_INACTIVE;
    return $this;
  }

  /**
   * Получить информацию о хэндлере
   * @return array|null
   */
  public function getHandler()
  {
    return ArrayHelper::getValue(static::getHandlers(), $this->handler_class_name);
  }

  /**
   * Названия всех возможных классов-обработчиков
   * @return array[]
   */
  public static function getHandlers()
  {
    return [
      static::HANDLER_ML => [
        'code' => static::HANDLER_ML,
        'name' => 'Mobleaders',
        'settingsClass' => ProviderSettingsMobleaders::class
      ],
      static::HANDLER_KP => [
        'code' => static::HANDLER_KP,
        'name' => 'KP',
        'settingsClass' => ProviderSettingsKp::class
      ],
      static::HANDLER_DEFAULT => [
        'code' => static::HANDLER_DEFAULT,
        'name' => 'Default',
        'settingsClass' => ProviderSettingsDefault::class
      ],
    ];
  }

  /**
   * Названия всех возможных классов-обработчиков в формате code => name
   * @return array
   */
  public static function getHandlerItems()
  {
    return ArrayHelper::map(static::getHandlers(), 'code', 'name');
  }

  /**
   * Получить настройки в виде объекта
   * @return AbstractProviderSettings
   */
  public function getSettings()
  {
    /** @var AbstractProviderSettings|string $settingsClass */
    /** @var AbstractProviderSettings|string $settings */
    $handler = $this->getHandler();
    if (!$handler) return null;
    $settingsClass = $handler['settingsClass'];

    $settings = new $settingsClass;
    /* Модель настроек заполняется данными, только если модель соответствует данным.
     * Например в модель настроек КП не надо подливать данные настроек Mobleaders
     * Проблему можно увидеть при редактировании провайдера -> смене хэндлера -> если у двух хэндлеров есть общие поля */
    if (!$this->isAttributeChanged('handler_class_name')) {
      $settings->attributes = Json::decode($this->settings);
    }

    return $settings;
  }

  /**
   * Установить настройки через объект
   * @param AbstractProviderSettings $settings
   * @return bool
   */
  public function setSettings(AbstractProviderSettings $settings)
  {
    if (!$settings->validate()) return false;

    $this->settings = Json::encode($settings->toArray());

    return true;
  }

  /**
   * определение внешнего провайдера по сценарию
   * @return bool
   */
  public function isExternalScenario()
  {
    return in_array($this->getScenario(), [
      self::SCENARIO_CREATE_EXTERNAL,
      self::SCENARIO_UPDATE_EXTERNAL
    ], true);
  }

  /**
   * Возможность просматривать и редактировать всех полей провайдера
   * @return bool
   */
  public static function canEditAllProviders()
  {
    return Yii::$app->user->can('CanEditAllProviders');
  }

  /**
   * Возвращает провайдеры не пренадлежащие RGK
   * @return array
   */
  public static function getNotRgkProviders()
  {
    return self::find()
      ->select('name')
      ->orderBy('name')
      ->where([
        'status' => self::STATUS_ACTIVE,
        'is_rgk' => 0
      ])
      ->indexBy('id')
      ->column();
  }
}