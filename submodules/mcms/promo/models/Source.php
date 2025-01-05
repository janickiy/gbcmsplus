<?php

namespace mcms\promo\models;

use mcms\common\AdminFormatter;
use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use mcms\common\validators\LocalhostUrlValidator;
use mcms\common\validators\UrlValidator;
use mcms\common\widget\modal\Modal;
use mcms\promo\components\api\Banners as BannerApi;
use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\components\AvailableOperators;
use mcms\promo\components\events\LinkStatusChanged;
use mcms\promo\components\events\SourceStatusChanged;
use mcms\promo\components\events\LinkCreated;
use mcms\promo\components\events\SourceCreated;
use mcms\promo\components\PrelandDefaultsSync;
use mcms\promo\components\SourceLandingSetsSync;
use mcms\promo\models\Banner;
use mcms\promo\models\traits\LinkBanners;
use mcms\promo\Module;
use mcms\promo\models\UserPromoSetting;
use Yii;
use mcms\user\models\User;
use yii\base\Exception;
use mcms\common\exceptions\ModelNotSavedException;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use mcms\common\helpers\Link;
use mcms\common\helpers\CIDR;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\validators\IpValidator;
use yii\validators\Validator;


/**
 * This is the model class for table "{{%sources}}".
 *
 * TRICKY: в классе @see SourceCopy не используются afterSave() и rules() данной базовой модели.
 *
 * @property integer $id
 * @property string $hash
 * @property integer $user_id
 * @property integer $default_profit_type
 * @property string $url
 * @property integer $allow_all_url
 * @property integer $ads_type
 * @property integer $status
 * @property integer $category_id
 * @property integer $set_id
 * @property integer $landing_set_autosync
 * @property integer $source_type
 * @property string $name
 * @property integer $stream_id
 * @property integer $domain_id
 * @property string $postback_url
 * @property integer $is_notify_subscribe
 * @property integer $is_notify_rebill
 * @property integer $is_notify_unsubscribe
 * @property integer $is_notify_cpa
 * @property integer $is_trafficback_sell
 * @property integer $trafficback_type
 * @property string $trafficback_url
 * @property string $label1
 * @property string $label2
 * @property string $subid1
 * @property string $subid2
 * @property string $cid
 * @property string $cid_value
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $filter_operators
 * @property string $operator_blocked_reason
 * @property integer $ads_network_id
 * @property string $reject_reason
 * @property integer $deleted_by
 * @property integer $banner_show_limit
 * @property integer $replace_links_css_class
 * @property integer $is_auto_rotation_enabled
 * @property integer $use_global_postback_url
 * @property integer $use_complains_global_postback_url
 * @property string $userLink @see Source::getUserLink()
 * @property string $link @see Source::getLink()
 * @property bool $is_traffic_filters_off Траф на моблидерсе проверяется в фильтрах. Для некоторых мегапроверенных ссылок у мегапроверенных партнеров будем отрубать этот фильтр передавая параметр foff=1 в МЛ. В провайдере КП этот параметр игнорируется (по крайней мере пока)
 * @property integer $send_all_get_params_to_pb
 *
 * @property Domain $domain
 * @property Banner[] $banners
 * @property Stream $stream
 * @property AdsNetwork $adsNetwork
 * @property User $user
 * @property SourceOperatorLanding[] $sourceOperatorLanding
 * @property Operator[] $offPrelandOperators
 * @property Operator[] $addPrelandOperators
 * @property Operator[] $blockedOperators
 * @property LandingConvertTest[] $landingConvertTests
 */
class Source extends \yii\db\ActiveRecord
{

  use Translate, LinkBanners;
  const LANG_PREFIX = 'promo.sources.';
  const LINK_BANNERS_TABLE = 'sources_banners';
  const LINK_BANNERS_FIELD = 'source_id';
  const LINK_BLOCKED_OPERATORS_TABLE = 'source_blocked_operators';
  const LINK_ADD_PRELAND_OPERATORS_TABLE = 'source_add_preland_operators';
  const LINK_OFF_PRELAND_OPERATORS_TABLE = 'source_off_preland_operators';

  const IS_AUTO_ROTATION_DISABLED = 0;
  const IS_AUTO_ROTATION_ENABLED = 1;

  // Отклонен администратором
  const STATUS_DECLINED = 0;
  // Заапрувлен администратором
  const STATUS_APPROVED = 1;
  // На модерации
  const STATUS_MODERATION = 2;
  // Удален партнером
  const STATUS_INACTIVE = 3;

  const SOURCE_TYPE_WEBMASTER_SITE = 1;
  const SOURCE_TYPE_LINK = 2;
  const SOURCE_TYPE_SMART_LINK = 3;

  const TRAFFICBACK_TYPE_STATIC = 1;
  const TRAFFICBACK_TYPE_DYNAMIC = 2;

  const IP_FORMAT_RANGE = 1;
  const IP_FORMAT_CIDR = 2;

  const BLOCKED_STRING = 'blocked';

  const SCENARIO_PARTNER_CREATE_WEBMASTER_SOURCE = 'parner_create_webmaster_source';
  const SCENARIO_ADMIN_UPDATE_WEBMASTER_SOURCE = 'admin_update_webmaster_source';
  const SCENARIO_ADMIN_UPDATE_ARBITRARY_SOURCE = 'admin_update_arbitrary_source';
  const SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE = 'parner_create_arbitrary_source';
  const SCENARIO_PARTNER_TEST_POSTBACK_URL = 'SCENARIO_PARTNER_TEST_POSTBACK_URL';
  const SCENARIO_PARTNER_UPDATE_WEBMASTER_SOURCE = 'parner_update_webmaster_source';
  const SCENARIO_ADMIN_CHANGE_STATUS = 'admin_change_status';
  const SCENARIO_ADMIN_UPDATE_ADD_OPERATOR_PRELAND = 'admin_update_add_operator_preland';
  const SCENARIO_ADMIN_UPDATE_OFF_OPERATOR_PRELAND = 'admin_update_off_operator_preland';
  const SCENARIO_ADMIN_SET_WEBMASTER_DECLINED_STATUS = 'admin_set_webmaster_declined_status';
  const SCENARIO_ADMIN_SET_DECLINED_ARBITRARY_SOURCE_STATUS = 'admin_set_declined_arbitrary_source';
  const SCENARIO_ADMIN_UPDATE_CATEGORY = 'admin_update_category';
  const SCENARIO_PARTNER_COPY = 'partner_copy';

  const DEFAULT_CID = 'cid';

  public $linkOperatorLandings;
  public $streamName;
  public $isNewStream = false;
  public $landingModels;
  public $forceLaunchConvertTest;
  /**
   * @var array ID операторов привязанных к ссылке для которых включен преленд
   */
  public $addPrelandOperatorIds = [];
  /**
   * @var array ID операторов привязанных к ссылке для которых выключен преленд
   */
  public $offPrelandOperatorIds = [];
  /**
   * @var array ID заблокированных операторов привязанных к ссылке
   */
  public $blockedOperatorIds = [];

  private $isLandingConvertTest;

  private $_old_status;

  public $stepNumber;

  /**
   * @return array
   */
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
    return '{{%sources}}';
  }

  /**
   * Фильтрация полей
   * @see \rgk\utils\helpers\FilterHelper
   */
  public function filterRules()
  {
    return [
      'label1' => false,
      'label2' => false,
      'subid1' => false,
      'subid2' => false,
    ];
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['url', 'postback_url', 'trafficback_url'], 'filter', 'filter' => function($value) {
          return str_replace(['"', "'"], '', $value);
      }],
      ['stepNumber', 'safe'],
      ['postback_url', 'required', 'when' => function ($attribute, $params) {
        if ($this->use_global_postback_url) return false;
        return !empty($this->is_notify_subscribe) ||
          !empty($this->is_notify_unsubscribe) ||
          !empty($this->is_notify_rebill) ||
          !empty($this->is_notify_cpa);
      }, 'enableClientValidation' => false], //TRICKY При условной валидации надо отключать клиентскую, иначе на клиенте поле всегда будет обязательным
      ['postback_url', 'chooseSubject', 'on' => self::SCENARIO_PARTNER_TEST_POSTBACK_URL],
      ['postback_url', LocalhostUrlValidator::class],
      ['postback_url', 'required', 'on' => self::SCENARIO_PARTNER_TEST_POSTBACK_URL],
      [['bannersIds', 'addPrelandOperatorIds', 'offPrelandOperatorIds', 'blockedOperatorIds'], 'each', 'rule' => ['integer']],
      ['banner_show_limit', 'integer'],
      ['replace_links_css_class', 'string', 'max' => '255'],
      [['url', 'status', 'ads_type', 'default_profit_type'], 'required', 'on' => self::SCENARIO_ADMIN_UPDATE_WEBMASTER_SOURCE],

      ['category_id', 'required', 'on' => self::SCENARIO_ADMIN_UPDATE_WEBMASTER_SOURCE, 'when' => function ($model) {
        return !$model->isDisabled();
      }],

      ['category_id', 'required', 'on' => self::SCENARIO_ADMIN_CHANGE_STATUS, 'when' => function ($model) {
        return $model->source_type == self::SOURCE_TYPE_WEBMASTER_SITE && $model->status == self::STATUS_APPROVED;
      }],

      [['forceLaunchConvertTest', 'addPrelandOperatorIds', 'offPrelandOperatorIds', 'blockedOperatorIds', 'operator_blocked_reason'], 'safe', 'on' => self::SCENARIO_ADMIN_UPDATE_WEBMASTER_SOURCE],
      ['forceLaunchConvertTest', 'checkForceLaunchConvertTest', 'on' => self::SCENARIO_ADMIN_UPDATE_WEBMASTER_SOURCE],
      [['addPrelandOperatorIds', 'offPrelandOperatorIds', 'blockedOperatorIds', 'operator_blocked_reason'], 'safe', 'on' => self::SCENARIO_ADMIN_UPDATE_ARBITRARY_SOURCE],

      [['url', 'ads_type', 'default_profit_type'], 'required', 'on' => self::SCENARIO_PARTNER_CREATE_WEBMASTER_SOURCE],
      ['id', function ($attribute) {
        /** Проверка правильности id источника. Чтоб не передали источник другого пользователя или источник с иным статусом. */
        if (!$this->$attribute) return;
        if ($this->user_id != Yii::$app->user->id || $this->status != self::STATUS_MODERATION)
          $this->addError($attribute, Yii::_t('promo.sources.wrong-webmaster-source-id'));
      }, 'on' => [self::SCENARIO_PARTNER_CREATE_WEBMASTER_SOURCE]],
      ['id', function ($attribute) {
        /** Проверка правильности id ссылки. Чтоб не передали ссылку другого пользователя или ссылку с иным статусом. */
        if (!$this->$attribute) return;
        /** @var \mcms\promo\Module $promoModule */
        $promoModule = Yii::$app->getModule('promo');
        $createLinkStatus = $promoModule->isArbitraryLinkModerationActive() ? self::STATUS_MODERATION : self::STATUS_APPROVED;
        if ($this->user_id != Yii::$app->user->id || ($this->isNewRecord && $this->status != $createLinkStatus))
          $this->addError($attribute, Yii::_t('promo.sources.wrong-webmaster-source-id'));
      }, 'on' => [self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE]],

      ['filter_operators', 'safe', 'on' => self::SCENARIO_PARTNER_CREATE_WEBMASTER_SOURCE],

      [['domain_id'], 'required', 'on' => self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE],
      [['linkOperatorLandings'], 'required', 'on' => self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE, 'message' => Yii::_t('promo.sources.landings_required'), 'when' => function (Source $model) {
        return !$model->isSmartLink();
      }],
      ['linkOperatorLandings', 'checkLinkOperatorLandings', 'on' => self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE],
      [['stream_id'], 'checkStream', 'skipOnEmpty' => false, 'on' => self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE],
      [['streamName', 'isNewStream', 'is_trafficback_sell'], 'safe', 'on' => self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE],
      ['streamName', 'unique', 'targetClass' => Stream::class, 'targetAttribute' => [
        'streamName' => 'name',
        'user_id' => 'user_id',
      ], 'when' => function (Source $model) {
        return $model->isNewStream;
      }, 'message' => Yii::_t('promo.streams.unique'), 'on' => self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE
      ],
      ['name', 'default', 'value' => $this->url, 'skipOnEmpty' => false,
        'on' => [self::SCENARIO_ADMIN_UPDATE_WEBMASTER_SOURCE, self::SCENARIO_PARTNER_CREATE_WEBMASTER_SOURCE]
      ],

      ['name', 'required'],
      [['landing_set_autosync'], 'filter', 'filter' => function () {
        return $this->isAutoRotationEnabled() ? 0 : $this->landing_set_autosync;
      }],
      [['landing_set_autosync', 'use_global_postback_url', 'use_complains_global_postback_url', 'send_all_get_params_to_pb'], 'safe'],
      [['landing_set_autosync', 'forceLaunchConvertTest'], function ($attribute) {
        if ($this->set_id && $this->landing_set_autosync && $this->forceLaunchConvertTest) {
          $this->addError($attribute, Yii::_t('promo.sources.set-autosync-and-convert-enable-error'));
          return false;
        }

        return true;
      }],
      [['set_id'], 'filter', 'filter' => function () {
        return $this->isAutoRotationEnabled() ? null : $this->set_id;
      }],
      ['set_id', 'currentCategorySet'],
      ['user_id', 'default', 'value' => isset(Yii::$app->user) ? Yii::$app->user->id : null],
      [['hash', 'user_id'], 'required'],
      [['user_id', 'default_profit_type', 'ads_type', 'status', 'source_type', 'stream_id', 'domain_id', 'is_notify_subscribe', 'is_notify_rebill', 'is_notify_unsubscribe', 'is_notify_cpa', 'trafficback_type', 'is_trafficback_sell', 'allow_all_url', 'is_auto_rotation_enabled'], 'integer'],
      [['url', 'name', 'trafficback_url', 'label1', 'label2', 'subid1', 'subid2', 'cid', 'cid_value'], 'string', 'max' => 255],
      [['label1', 'label2', 'subid1', 'subid2', 'cid', 'cid_value'], 'trim'],
      [['postback_url', 'trafficback_url'], UrlValidator::class, 'enableIDN' => true],
      [['hash', 'url'], 'unique'],
      ['url', UrlValidator::class, 'enableIDN' => true],
      [['domain_id'], 'exist', 'skipOnError' => true, 'targetClass' => Domain::class, 'targetAttribute' => ['domain_id' => 'id'],
        'filter' => ['and',
          ['=', 'status', Domain::STATUS_ACTIVE],
          ['or',
            ['=', 'is_system', true],
            ['=', 'user_id', $this->user_id],
          ]
        ],
        'message' => Yii::_t('promo.domains.select_active')
      ],
      [['stream_id'], 'exist', 'skipOnError' => true, 'targetClass' => Stream::class, 'targetAttribute' => ['stream_id' => 'id'],
        'filter' => ['=', 'user_id', $this->user_id],
        'when' => function (Source $model) {
          return !($model->isNewStream);
        }
      ],
      [
        'stream_id',
        function ($attribute) {
          if ($this->stream->isDisabled()) {
            $this->addError($attribute, Yii::_t('promo.sources.stream_status-error'));
          }
        },
        'on' => [self::SCENARIO_ADMIN_UPDATE_ARBITRARY_SOURCE, self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE],
        'when' => function (Source $model) {
          return !$model->isNewStream && !$model->isSmartLink();
        }
      ],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
      [['ads_network_id'], 'exist', 'skipOnError' => true, 'targetClass' => AdsNetwork::class, 'targetAttribute' => ['ads_network_id' => 'id']],

      ['reject_reason', 'required', 'when' => function ($model) {
        return $model->isDeclined();
      }, 'whenClient' => "function(){}"],
      ['reject_reason', 'string'],

      ['default_profit_type', 'in', 'range' => array_keys(SourceOperatorLanding::getProfitTypes())],

      ['deleted_by', 'exist', 'targetClass' => User::class, 'targetAttribute' => ['deleted_by' => 'id']],
      [['is_traffic_filters_off'], 'default', 'value' => 0],
      ['is_traffic_filters_off', 'canManageTrafficFiltersOff', 'on' => [self::SCENARIO_ADMIN_UPDATE_ARBITRARY_SOURCE]],
      ['use_global_postback_url', 'isGlobalPostbackUrlExist'],
      ['use_complains_global_postback_url', 'isGlobalComplainsPostbackUrlExist'],
    ];
  }

  /**
   * @return array
   */
  public function scenarios()
  {
    return [
      self::SCENARIO_ADMIN_UPDATE_WEBMASTER_SOURCE => [
        'url',
        'name',
        'status',
        'ads_type',
        'category_id',
        'set_id',
        'landing_set_autosync',
        'forceLaunchConvertTest',
        'addPrelandOperatorIds',
        'offPrelandOperatorIds',
        'blockedOperatorIds',
        'operator_blocked_reason',
        'reject_reason',
        'landingModels',
        'allow_all_url',
        'banner_show_limit',
        'bannersIds',
        'replace_links_css_class',
        'is_auto_rotation_enabled',
      ],
      self::SCENARIO_ADMIN_UPDATE_ARBITRARY_SOURCE => [
        'name',
        'status',
        'stream_id',
        'domain_id',
        'postback_url',
        'trafficback_url',
        'label1',
        'label2',
        'subid1',
        'subid2',
        'cid',
        'cid_value',
        'is_notify_subscribe',
        'is_notify_rebill',
        'is_notify_unsubscribe',
        'is_notify_cpa',
        'trafficback_type',
        'is_trafficback_sell',
        'addPrelandOperatorIds',
        'offPrelandOperatorIds',
        'blockedOperatorIds',
        'operator_blocked_reason',
        'reject_reason',
        'landingModels',
        'is_allow_force_operator',
        'addPrelandOperatorIds',
        'offPrelandOperatorIds',
        'blockedOperatorIds',
        'is_traffic_filters_off',
        'use_global_postback_url',
        'use_complains_global_postback_url',
        'send_all_get_params_to_pb',
      ],
      self::SCENARIO_PARTNER_CREATE_WEBMASTER_SOURCE => [
        'id',
        'url',
        'name',
        'ads_type',
        'default_profit_type',
        'filter_operators',
        'allow_all_url',
      ],
      self::SCENARIO_PARTNER_UPDATE_WEBMASTER_SOURCE => ['ads_type', 'default_profit_type', 'filter_operators'],
      self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE => [
        'name',
        'stream_id',
        'domain_id',
        'postback_url',
        'trafficback_url',
        'label1',
        'label2',
        'subid1',
        'subid2',
        'cid',
        'cid_value',
        'is_notify_subscribe',
        'is_notify_rebill',
        'is_notify_unsubscribe',
        'is_notify_cpa',
        'trafficback_type',
        'isNewStream',
        'streamName',
        'id',
        'linkOperatorLandings',
        'is_trafficback_sell',
        'ads_network_id',
        'stepNumber',
        'use_global_postback_url',
        'use_complains_global_postback_url',
        'send_all_get_params_to_pb',
      ],
      self::SCENARIO_PARTNER_TEST_POSTBACK_URL => [
        'postback_url',
        'stepNumber',
        'is_notify_subscribe',
        'is_notify_unsubscribe',
        'is_notify_rebill',
        'is_notify_cpa',
      ],
      self::SCENARIO_ADMIN_CHANGE_STATUS => ['status', 'category_id', 'reject_reason'],
      self::SCENARIO_DEFAULT => array_keys($this->getAttributes()),
      self::SCENARIO_ADMIN_UPDATE_ADD_OPERATOR_PRELAND => ['addPrelandOperatorIds'],
      self::SCENARIO_ADMIN_UPDATE_OFF_OPERATOR_PRELAND => ['offPrelandOperatorIds'],
      self::SCENARIO_ADMIN_SET_DECLINED_ARBITRARY_SOURCE_STATUS => ['status', 'reject_reason'],
      self::SCENARIO_ADMIN_SET_WEBMASTER_DECLINED_STATUS => ['status', 'reject_reason'],
      self::SCENARIO_ADMIN_UPDATE_CATEGORY => ['category_id'],

      // некоторые поля запрещаем копировать из ссылки в ссылку
      self::SCENARIO_PARTNER_COPY => array_filter(
        array_keys($this->getAttributes()),
        function ($attribute) {
          return !in_array($attribute, [
            'is_allow_force_operator',
            'is_traffic_filters_off',
            'operator_blocked_reason',
          ], true);
        }
      ),
    ];
  }

  /**
   * Валидация при тестировании Postback Url
   * @param $attribute
   * @param $params
   * @return bool
   */
  public function chooseSubject($attribute, $params)
  {
    if (
      empty($this->is_notify_subscribe) &&
      empty($this->is_notify_unsubscribe) &&
      empty($this->is_notify_rebill) &&
      empty($this->is_notify_cpa)
    ) {
      $this->addError($attribute, Yii::_t('promo.sources.choose_subject'));
      return false;
    }
    return true;
  }

  public function currentCategorySet($attribute, $params)
  {
    $set = $this->getLandingSet()->one();
    if ($set->category_id != $this->category_id && $set->category_id) {
      $this->addError($attribute, self::translate('set_id-category_id-error'));
    }
  }

  public function checkStream($attribute, $params)
  {
    if ($this->isNewStream && !$this->streamName) {
      $this->addError('streamName', self::translate('stream_name-error'));
    }
    if (!$this->isNewStream && !$this->stream_id) {
      $this->addError('stream_id', self::translate('stream_id-error'));
    }
  }

  public function checkForceLaunchConvertTest($attribute, $params)
  {
    if ($this->$attribute && $this->status != self::STATUS_APPROVED) {
      $this->addError($attribute, self::translate('force-launch-convert-test-error-status'));
    }
  }

  public function checkLinkOperatorLandings($attribute, $params)
  {
    if (!$this->$attribute || !is_array($this->$attribute)) return;

    /** @var Landing[] $landings */
    $landingsCount = Landing::find()
      ->where([
        'and',
        ['id' => array_keys($this->$attribute)],
        ['status' => Landing::STATUS_ACTIVE],
      ])
      ->count();

    if ($landingsCount != count($this->$attribute)) {
      $this->addError($attribute, Yii::_t('promo.sources.wrong-landings-selected-error'));
      return;
    }
  }

  public function beforeValidate()
  {
    if (($this->scenario == self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE || $this->scenario == self::SCENARIO_PARTNER_UPDATE_WEBMASTER_SOURCE)
      && !$this->isNewRecord && $this->isBlocked()) {
      $this->addError('status', self::translate('blocked-error'));

      return false;
    }

    if ($this->scenario == self::SCENARIO_PARTNER_CREATE_WEBMASTER_SOURCE) {
      $domain = Domain::getActiveSystemDomain();

      if (empty($domain)) {
        $this->addError('domain_id', self::translate('no-domains-found-error'));
        return false;
      }
    }

    return parent::beforeValidate();
  }

  public function beforeSave($insert)
  {
    $this->createStreamIfNew();
    // todo может удалить? есть правило default в рулесах
    $this->name = $this->source_type == self::SOURCE_TYPE_WEBMASTER_SITE ? $this->url : $this->name;
    if ($this->trafficback_type == self::TRAFFICBACK_TYPE_DYNAMIC) $this->trafficback_url = '';

    if (is_array($this->filter_operators)) $this->filter_operators = json_encode($this->filter_operators);

    /**
     * Подставляем значения прелендов по-умолчанию
     * @var PrelandDefaults $defaultPrelands
     */
    if ($insert) {
      $defaultAddPrelands = $this->findDefaultPrelandOperators(PrelandDefaults::TYPE_ADD);
      $operators = [];
      foreach ($defaultAddPrelands->each() as $defaultPrelandAdd) {
        $operators = array_merge($operators, $defaultPrelandAdd->operators);
      }

      if ($defaultAddPrelands) {
        $this->addPrelandOperatorIds = $operators;
      }

      $defaultOffPrelands = $this->findDefaultPrelandOperators(PrelandDefaults::TYPE_ADD);
      $operators = [];
      foreach ($defaultOffPrelands->each() as $defaultPrelandAdd) {
        $operators = array_merge($operators, $defaultPrelandAdd->operators);
      }

      if ($defaultOffPrelands) {
        $this->offPrelandOperatorIds = $operators;
      }
    }

    return parent::beforeSave($insert);
  }

  private function createStreamIfNew()
  {
    if ($this->scenario != self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE || !$this->isNewStream) return;

    $stream = new Stream([
      'name' => $this->streamName,
      'status' => Stream::STATUS_ACTIVE
    ]);

    if (!$stream->save()) {
      throw new Exception('Stream save error');
    }
    $this->stream_id = $stream->id;
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'hash',
      'link',
      'user_id',
      'default_profit_type',
      'url',
      'allow_all_url',
      'ads_type',
      'status',
      'category_id',
      'set_id',
      'source_type',
      'name',
      'stream_id',
      'domain_id',
      'postback_url',
      'is_notify_subscribe',
      'is_notify_rebill',
      'is_notify_unsubscribe',
      'is_notify_cpa',
      'is_trafficback_sell',
      'trafficback_type',
      'trafficback_url',
      'label1',
      'label2',
      'subid1',
      'subid2',
      'cid',
      'cid_value',
      'created_at',
      'updated_at',
      'operatorLandingLinks',
      'forceLaunchConvertTest',
      'addPrelandOperatorIds',
      'offPrelandOperatorIds',
      'blockedOperatorIds',
      'addPrelandOperatorNames',
      'offPrelandOperatorNames',
      'blockedOperatorNames',
      'operator_blocked_reason',
      'reject_reason',
      'deleted_by',
      'banner_show_limit',
      'landing_set_autosync',
      'is_allow_force_operator',
      'replace_links_css_class',
      'is_traffic_filters_off',
      'is_auto_rotation_enabled',
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getDomain()
  {
    return $this->hasOne(Domain::class, ['id' => 'domain_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getStream()
  {
    return $this->hasOne(Stream::class, ['id' => 'stream_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getAdsNetwork()
  {
    return $this->hasOne(AdsNetwork::class, ['id' => 'ads_network_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCategory()
  {
    return $this->hasOne(LandingCategory::class, ['id' => 'category_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSourceOperatorLanding()
  {
    return $this->hasMany(SourceOperatorLanding::class, ['source_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getActiveSourceOperatorLanding()
  {
    return $this->hasMany(SourceOperatorLanding::class, ['source_id' => 'id'])
      ->joinWith(['operator.country', 'landing'])
      ->andWhere([
        'countries.status' => Country::STATUS_ACTIVE,
        'operators.status' => Operator::STATUS_ACTIVE,
        'landings.status' => Landing::STATUS_ACTIVE,
      ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLandingConvertTests()
  {
    return $this->hasMany(LandingConvertTest::class, ['source_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLastLandingConvertTest()
  {
    return $this->hasOne(LandingConvertTest::class, ['source_id' => 'id'])
      ->orderBy(['created_at' => SORT_DESC])
      ->limit(1);
  }

  /**
   * @param null $status
   * @return array|mixed
   */
  public static function getStatuses($status = null)
  {
    $list = [
      self::STATUS_DECLINED => self::translate('status-declined'),
      self::STATUS_APPROVED => self::translate('status-approved'),
      self::STATUS_MODERATION => self::translate('status-moderation'),
      self::STATUS_INACTIVE => self::translate('status-inactive'),
    ];
    return isset($status) ? ArrayHelper::getValue($list, $status, null) : $list;
  }

  /**
   * @param bool|true $activeOnly
   * @param null $category
   * @return array
   */
  public function getCategories($category = null, $activeOnly = true)
  {
    $list = LandingCategory::getAllMap($activeOnly);
    return $category ? ArrayHelper::getValue($list, $category) : $list;
  }

  /**
   * @return array|mixed
   */
  public function getStatus()
  {
    return $this->getStatuses($this->status);
  }

  /**
   * @return bool
   */
  public function isStatusModeration()
  {
    return $this->status == self::STATUS_MODERATION;
  }

  /**
   * @return array|mixed
   */
  public function getCurrentStatusName()
  {
    return $this->getStatuses($this->status);
  }

  /**
   * @return array|mixed
   */
  public function getStatusChangedName()
  {
    $list = [
      self::SOURCE_TYPE_WEBMASTER_SITE =>
        [
          self::STATUS_MODERATION => Yii::_t('promo.replacements.source_status_moderation', ['name' => $this->name]),
          self::STATUS_INACTIVE => Yii::_t('promo.replacements.source_status_inactive', ['name' => $this->name]),
          self::STATUS_APPROVED => Yii::_t('promo.replacements.source_status_approved', ['name' => $this->name]),
          self::STATUS_DECLINED => Yii::_t('promo.replacements.source_status_declined', ['name' => $this->name]),
        ],
      self::SOURCE_TYPE_LINK =>
        [
          self::STATUS_MODERATION => Yii::_t('promo.replacements.link_status_moderation', ['name' => $this->name]),
          self::STATUS_INACTIVE => Yii::_t('promo.replacements.link_status_inactive', ['name' => $this->name]),
          self::STATUS_APPROVED => Yii::_t('promo.replacements.link_status_approved', ['name' => $this->name]),
          self::STATUS_DECLINED => Yii::_t('promo.replacements.link_status_declined', ['name' => $this->name]),
        ]
    ];

    return $list[$this->source_type][$this->status];
  }

  /**
   * @return array|mixed
   */
  public function getCurrentCategoryName()
  {
    return $this->category_id ? $this->getCategories($this->category_id) : null;
  }

  public function setActive()
  {
    $this->status = self::STATUS_APPROVED;
    return $this;
  }

  public function setInActive()
  {
    $this->status = self::STATUS_INACTIVE;
    return $this;
  }

  public function setDeclined()
  {
    $this->status = self::STATUS_DECLINED;
    return $this;
  }

  /**
   * @param $length
   * @throws Exception
   */
  public function initHash($length = 10)
  {
    if (!$this->hash) {
      $this->hash = self::generateHash($length);
    }
  }

  /**
   * @param $length
   * @return string
   * @throws Exception
   */
  public static function generateHash($length = 10)
  {
    return Yii::$app->security->generateRandomString($length);
  }

  /**
   * @return bool
   */
  public function isDisabled()
  {
    return $this->status != self::STATUS_APPROVED;
  }

  /**
   * @return bool
   */
  public function isInactive()
  {
    return $this->status == self::STATUS_INACTIVE;
  }

  public function isDeclined()
  {
    return $this->status == self::STATUS_DECLINED;
  }

  /**
   * @return bool
   */
  public function isAutoRotationEnabled()
  {
    /** @var Module $module */
    $module = Yii::$app->getModule('promo');

    return $module->getIsLandingsAutoRotationGlobalEnabled()
      && $this->is_auto_rotation_enabled == self::IS_AUTO_ROTATION_ENABLED;
  }

  /**
   * @return bool
   */
  public function isBlocked()
  {
    return $this->status == self::STATUS_INACTIVE || $this->status == self::STATUS_DECLINED;
  }

  /**
   * Возвращает BLOCKED_STRING, если нельзя редактировать, или пустую строку в противном случае
   * Необходимо для корректной работы кнопок партнерского кабинете
   * @return string
   */
  public function isBlockedString()
  {
    return $this->isBlocked() ? self::BLOCKED_STRING : '';
  }

  /**
   * @return bool
   */
  public function isEnabled()
  {
    return $this->status == self::STATUS_APPROVED;
  }

  /**
   * @return array
   */
  static function getStatusColors()
  {
    return [
      self::STATUS_INACTIVE => 'danger',
      self::STATUS_APPROVED => 'success',
      self::STATUS_MODERATION => 'warning',
      self::STATUS_DECLINED => 'danger',
    ];
  }


  /**
   * @return array|mixed
   */
  public function getAdsType()
  {
    return AdsType::findById($this->ads_type);
  }


  /**
   * @return array|mixed
   */
  public function getCurrentAdsTypeName()
  {
    $model = AdsType::findById($this->ads_type);
    return $model ? $model->name : null;
  }

  /**
   * @param null $value
   * @return array|mixed
   */
  public static function getSourceTypes($value = null)
  {
    $list = [
      self::SOURCE_TYPE_LINK => self::translate('source_type-link'),
      self::SOURCE_TYPE_WEBMASTER_SITE => self::translate('source_type-webmaster_site'),
    ];
    return isset($value) ? ArrayHelper::getValue($list, $value, null) : $list;
  }

  /**
   * @return array|mixed
   */
  public function getCurrentSourceTypeName()
  {
    return $this->getSourceTypes($this->ads_type);
  }

  /**
   * @param null $value
   * @return array|mixed
   */
  public static function getTrafficbackTypes($value = null)
  {
    $list = [
      self::TRAFFICBACK_TYPE_STATIC => self::translate('trafficback_type-static'),
      self::TRAFFICBACK_TYPE_DYNAMIC => self::translate('trafficback_type-dynamic'),
    ];
    return isset($value) ? ArrayHelper::getValue($list, $value, null) : $list;
  }

  /**
   * @return array|mixed
   */
  public function getCurrentTrafficbackTypeName()
  {
    return $this->getTrafficbackTypes($this->trafficback_type);
  }

  /**
   * @return array|mixed
   */
  public function getDefaultProfitTypeName()
  {
    return (new SourceOperatorLanding())->getProfitTypes($this->default_profit_type);
  }

  /**
   * @param bool $insert
   * @param array $changedAttributes
   */
  public function afterSave($insert, $changedAttributes)
  {
    if ($insert) {
      if ($this->source_type == self::SOURCE_TYPE_LINK) (new LinkCreated($this))->trigger();
      if ($this->source_type == self::SOURCE_TYPE_WEBMASTER_SITE) (new SourceCreated($this))->trigger();
    }

    if (!$insert && $this->_old_status != $this->status) {
      if ($this->source_type == self::SOURCE_TYPE_LINK) {
        (new LinkStatusChanged($this))->trigger();
      }

      if ($this->source_type == self::SOURCE_TYPE_WEBMASTER_SITE) {
        (new SourceStatusChanged($this))->trigger();
      }
    }

    $this->saveLinkOperatorLandings($insert);

    if ($insert) {
      $this->saveLinkAddPrelandOperators();
    }
    $this->saveLinkOffPrelandOperators($insert);
    $this->saveLinkBlockedOperators($insert);

    if (!$this->set_id || !$this->landing_set_autosync) {
      $this->beginLandingsConvertTest($changedAttributes);
    }
    if (empty($changedAttributes['landing_set_autosync']) && $this->landing_set_autosync && $this->set_id) {
      LandingConvertTest::forceFinishAllTests($this->id);
      (new SourceLandingSetsSync(['sourceId' => $this->id]))->run();
    }

    $this->updateProfitType($insert, $changedAttributes);

    (new PrelandDefaultsSync([
      'type' => [PrelandDefaults::TYPE_ADD, PrelandDefaults::TYPE_OFF],
      'sourceId' => $this->id,
      'userId' => $this->user_id,
      'streamId' => $this->stream_id,
    ]))->run();

    $this->invalidateCache();

    parent::afterSave($insert, $changedAttributes);
  }

  public function afterFind()
  {
    parent::afterFind();
    // TODO всегда эти релейшены достаются, даже когда нахер не нужны. Например в апи фильтров статы
    $this->offPrelandOperatorIds = ArrayHelper::getColumn($this->offPrelandOperators, 'id');
    $this->addPrelandOperatorIds = ArrayHelper::getColumn($this->addPrelandOperators, 'id');
    $this->blockedOperatorIds = ArrayHelper::getColumn($this->blockedOperators, 'id');
    $this->_old_status = $this->status;
  }

  public function isLandingsConvertTest()
  {

    if (!is_null($this->isLandingConvertTest)) return $this->isLandingConvertTest;

    if ( // Обязательные условия запуска теста
      (
        $this->scenario != self::SCENARIO_ADMIN_UPDATE_WEBMASTER_SOURCE &&
        $this->scenario != self::SCENARIO_ADMIN_CHANGE_STATUS
      ) ||
      $this->status != self::STATUS_APPROVED ||
      empty($this->category_id) ||
      $this->source_type != self::SOURCE_TYPE_WEBMASTER_SITE
    ) return $this->isLandingConvertTest = false;

    // Если тесты уже были для этого источника, и флаг принудительного запуска выключен
    if (count($this->landingConvertTests) > 0 && !$this->forceLaunchConvertTest) return $this->isLandingConvertTest = false;

    return $this->isLandingConvertTest = true;
  }

  /**
   * Запускаем тестирование конверта для источника
   */
  private function beginLandingsConvertTest($changedAttributes)
  {

    if (!$this->isLandingsConvertTest()) return;

    $testConvert = new LandingConvertTest([
      'source_id' => $this->id,
      'status' => LandingConvertTest::STATUS_ACTIVE,
      'scenario' => LandingConvertTest::SCENARIO_TEST_CREATE
    ]);

    if (!$testConvert->save()) {
      throw new Exception('Landing test convert model save error');
    }
  }

  /**
   * @param $insert
   * @throws Exception
   */
  private function saveLinkOperatorLandings($insert)
  {

    if ($this->scenario != self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE) return;

    if ($this->isLandingsConvertTest()) return;

    $oldIDs = $insert ? [] : ArrayHelper::map($this->getSourceOperatorLanding()->all(), 'id', 'id');
    $newIds = [];
    $clearCacheKeys = [];

    if (!is_array($this->linkOperatorLandings)) {
      $this->linkOperatorLandings = [];
    }

    foreach ($this->linkOperatorLandings as $landingId => $operators) {
      foreach ($operators as $operatorId => $info) {
        $model = SourceOperatorLanding::findOrCreateModelForLink($this->id, $operatorId, $landingId);

        $model->setAttributes($info);

        if (!$model->save()) {
          throw new Exception('Landing operator model save error');
        }

        $newIds[] = $model->id;
        $clearCacheKeys[] = 'Source' . $this->id . 'Operator' . $operatorId . 'LandingsCount';
      }
    }

    ApiHandlersHelper::clearCache($clearCacheKeys);

    $deletedIDs = array_diff($oldIDs, $newIds);

    if (!empty($deletedIDs)) SourceOperatorLanding::deleteAll(['id' => $deletedIDs]);
  }

  /**
   * @throws Exception
   */
  protected function saveLinkAddPrelandOperators()
  {
    $transaction = Yii::$app->db->beginTransaction();
    try {
      $oldAddPrelandOperators = [];

      /**
       * добавляем новые связи
       */
      $this->addPrelandOperatorIds = empty($this->addPrelandOperatorIds) ? [] : $this->addPrelandOperatorIds;

      foreach ($this->addPrelandOperatorIds as $addPrelandOperatorId) {
        if (
          !in_array($addPrelandOperatorId, ArrayHelper::getColumn($oldAddPrelandOperators, 'id')) &&
          $operatorModel = Operator::findOne($addPrelandOperatorId)
        ) {
          $this->link('addPrelandOperators', $operatorModel);
        }
      }

      /**
       * удаляем старые связи
       */
      foreach ($oldAddPrelandOperators as $oldAddPrelandOperator) {
        if (!in_array($oldAddPrelandOperator->id, $this->addPrelandOperatorIds)) {
          $this->unlink('addPrelandOperators', $oldAddPrelandOperator, true);
        }
      }
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollBack();
      throw $e;
    }
  }

  /**
   * @param $insert
   * @throws Exception
   */
  protected function saveLinkOffPrelandOperators($insert)
  {
    $transaction = Yii::$app->db->beginTransaction();
    try {
      $oldOffPrelandOperators = $insert ? [] : $this->offPrelandOperators;

      /**
       * добавляем новые связи
       */
      $this->offPrelandOperatorIds = empty($this->offPrelandOperatorIds) ? [] : $this->offPrelandOperatorIds;

      foreach ($this->offPrelandOperatorIds as $offPrelandOperatorId) {
        if (
          !in_array($offPrelandOperatorId, ArrayHelper::getColumn($oldOffPrelandOperators, 'id')) &&
          $operatorModel = Operator::findOne($offPrelandOperatorId)
        ) {
          $this->link('offPrelandOperators', $operatorModel);
        }
      }

      /**
       * удаляем старые связи
       */
      foreach ($oldOffPrelandOperators as $oldOffPrelandOperator) {
        if (!in_array($oldOffPrelandOperator->id, $this->offPrelandOperatorIds)) {
          $this->unlink('offPrelandOperators', $oldOffPrelandOperator, true);
        }
      }
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollBack();
      throw $e;
    }
  }

  /**
   * @param $insert
   * @throws Exception
   */
  protected function saveLinkBlockedOperators($insert)
  {
    $transaction = Yii::$app->db->beginTransaction();
    try {
      $oldBlockedOperators = $insert ? [] : $this->blockedOperators;

      /**
       * добавляем новые связи
       */
      $this->blockedOperatorIds = empty($this->blockedOperatorIds) ? [] : $this->blockedOperatorIds;

      foreach ($this->blockedOperatorIds as $blockedOperatorId) {
        if (
          !in_array($blockedOperatorId, ArrayHelper::getColumn($oldBlockedOperators, 'id')) &&
          $operatorModel = Operator::findOne($blockedOperatorId)
        ) {
          $this->link('blockedOperators', $operatorModel);
        }
      }

      /**
       * удаляем старые связи
       */
      foreach ($oldBlockedOperators as $oldBlockedOperator) {
        if (!in_array($oldBlockedOperator->id, $this->blockedOperatorIds)) {
          $this->unlink('blockedOperators', $oldBlockedOperator, true);
        }
      }
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollBack();
      throw $e;
    }
  }


  /**
   * Данный кэш создается и используется в апи Source.
   * При изменении текущей модели необходимо сбрасывать кэш для источника.
   */
  protected function invalidateCache()
  {
    Yii::$app->cache->delete('source__' . $this->hash);
    ApiHandlersHelper::clearCache('SourceData' . $this->hash);
    ApiHandlersHelper::clearCache('SourceDataById' . $this->id);
    ApiHandlersHelper::clearCache('SourceLandingIdsGroupByOperator' . $this->id);
    ApiHandlersHelper::clearCache('source-get-selected-banner' . $this->id);
    ApiHandlersHelper::invalidateTags('SourceData' . $this->hash);
    BannerApi::clearSelectedBannerCache();
  }

  /**
   * Получаем postback url
   * @return null|string
   */
  public function getPostbackUrl()
  {
    return $this->use_global_postback_url ? UserPromoSetting::getGlobalPostbackUrl() : $this->postback_url;
  }

  /**
   * Получаем postback url для жалоб
   * @return string
   */
  public function getComplainsPostbackUrl()
  {
    return $this->use_complains_global_postback_url ? UserPromoSetting::getGlobalComplainsPostbackUrl() : '';
  }


  public function getReplacements()
  {
    /** @var User $user */
    $user = $this->getUser()->one();

    /** @var Stream $stream */
    $stream = $this->getStream()->one();

    /** @var Domain $domain */
    $domain = $this->getDomain()->one();

    /** @var AdsNetwork $adsNetwork */
    $adsNetwork = $this->getAdsNetwork()->one();
    return [
      'id' => [
        'value' => $this->isNewRecord ? null : $this->id,
        'help' => [
          'label' => self::translate('replacement-id')
        ]
      ],
      'hash' => [
        'value' => $this->isNewRecord ? null : $this->hash,
        'help' => [
          'label' => self::translate('replacement-hash')
        ]
      ],
      'reject_reason' => [
        'value' => $this->reject_reason,
        'help' => [
          'label' => self::translate('reject_reason'),
        ],
      ],
      'user' => [
        'value' => $this->isNewRecord ? null : $user->getReplacements(),
        'help' => [
          'class' => Yii::$app->user->identityClass,
          'label' => self::translate('replacement-user')
        ]
      ],
      'default_profit_type' => [
        'value' => $this->isNewRecord ? null : (new SourceOperatorLanding())->getProfitTypes($this->default_profit_type),
        'help' => [
          'label' => self::translate('replacement-default_profit_type')
        ]
      ],
      'url' => [
        'value' => $this->isNewRecord ? null : $this->url,
        'help' => [
          'label' => self::translate('replacement-url')
        ]
      ],
      'ads_type' => [
        'value' => $this->isNewRecord ? null : $this->getCurrentAdsTypeName(),
        'help' => [
          'label' => self::translate('replacement-ads_type')
        ]
      ],
      'status' => [
        'value' => $this->isNewRecord ? null : $this->getCurrentStatusName(),
        'help' => [
          'label' => self::translate('replacement-status')
        ]
      ],
      'status_changed' => [
        'value' => $this->isNewRecord ? null : $this->getStatusChangedName(),
        'help' => [
          'label' => self::translate('replacement-status-changed')
        ]
      ],
      'source_type' => [
        'value' => $this->isNewRecord ? null : $this->getCurrentSourceTypeName(),
        'help' => [
          'label' => self::translate('replacement-source_type')
        ]
      ],
      'name' => [
        'value' => $this->isNewRecord ? null : $this->name,
        'help' => [
          'label' => self::translate('replacement-name')
        ]
      ],
      'stream' => [
        'value' => $this->isNewRecord || !$stream ? null : $stream->getReplacements(),
        'help' => [
          'class' => Stream::class,
          'label' => self::translate('replacement-stream')
        ]
      ],
      'domain' => [
        'value' => $this->isNewRecord || !$domain ? null : $domain->getReplacements(),
        'help' => [
          'label' => self::translate('replacement-domain'),
          'class' => Domain::class
        ]
      ],
      'postback_url' => [
        'value' => $this->isNewRecord ? null : $this->getPostbackUrl(),
        'help' => [
          'label' => self::translate('replacement-postback_url')
        ]
      ],
      'is_notify_subscribe' => [
        'value' => $this->isNewRecord ? null : $this->getReplacementIsNotifySubscribe(),
        'help' => [
          'label' => self::translate('replacement-is_notify_subscribe')
        ]
      ],
      'is_notify_rebill' => [
        'value' => $this->isNewRecord ? null : $this->getReplacementIsNotifyRebill(),
        'help' => [
          'label' => self::translate('replacement-is_notify_rebill')
        ]
      ],
      'is_notify_unsubscribe' => [
        'value' => $this->isNewRecord ? null : $this->getReplacementIsNotifyUnsubscribe(),
        'help' => [
          'label' => self::translate('replacement-is_notify_unsubscribe')
        ]
      ],
      'is_notify_cpa' => [
        'value' => $this->isNewRecord ? null : $this->getReplacementIsNotifySell(),
        'help' => [
          'label' => self::translate('replacement-is_notify_cpa')
        ]
      ],
      'is_trafficback_sell' => [
        'value' => $this->isNewRecord ? null : $this->getReplacementIsTrafficbackSell(),
        'help' => [
          'label' => self::translate('replacement-is_trafficback_sell')
        ]
      ],
      'trafficback_type' => [
        'value' => $this->isNewRecord ? null : $this->getCurrentTrafficbackTypeName(),
        'help' => [
          'label' => self::translate('replacement-trafficback_type')
        ]
      ],
      'trafficback_url' => [
        'value' => $this->isNewRecord ? null : $this->trafficback_url,
        'help' => [
          'label' => self::translate('replacement-trafficback_url')
        ]
      ],
      'label1' => [
        'value' => $this->isNewRecord ? null : $this->label1,
        'help' => [
          'label' => self::translate('replacement-label1')
        ]
      ],
      'label2' => [
        'value' => $this->isNewRecord ? null : $this->label2,
        'help' => [
          'label' => self::translate('replacement-label2')
        ]
      ],
      'subid1' => [
        'value' => $this->isNewRecord ? null : $this->subid1,
        'help' => [
          'label' => self::translate('replacement-subid1')
        ]
      ],
      'subid2' => [
        'value' => $this->isNewRecord ? null : $this->subid2,
        'help' => [
          'label' => self::translate('replacement-subid2')
        ]
      ],
      'cid' => [
        'value' => $this->isNewRecord ? null : $this->cid,
        'help' => [
          'label' => self::translate('replacement-cid')
        ]
      ],
      'cid_value' => [
        'value' => $this->isNewRecord ? null : $this->cid_value,
        'help' => [
          'label' => self::translate('replacement-cid_value')
        ]
      ],
      'forceLaunchConvertTest' => [
        'value' => Yii::_t($this->forceLaunchConvertTest ? 'app.common.Yes' : 'app.common.No'),
        'help' => [
          'label' => self::translate('replacement-forceLaunchConvertTest')
        ]
      ],
      'ads_network' => [
        'value' => $this->isNewRecord || !$adsNetwork ? null : $adsNetwork->getReplacements(),
        'help' => [
          'label' => self::translate('replacement-ads_network'),
          'class' => AdsNetwork::class
        ]
      ],
    ];
  }

  /**
   * @return string
   */
  public function getReplacementIsNotifySubscribe()
  {
    return Yii::_t($this->is_notify_subscribe ? 'app.common.Yes' : 'app.common.No');
  }

  /**
   * @return string
   */
  public function getReplacementIsNotifyUnsubscribe()
  {
    return Yii::_t($this->is_notify_unsubscribe ? 'app.common.Yes' : 'app.common.No');
  }

  /**
   * @return string
   */
  public function getReplacementIsNotifySell()
  {
    return Yii::_t($this->is_notify_cpa ? 'app.common.Yes' : 'app.common.No');
  }

  public function getReplacementIsTrafficbackSell()
  {
    return Yii::_t($this->is_trafficback_sell ? 'app.common.Yes' : 'app.common.No');
  }

  /**
   * @return string
   */
  public function getReplacementIsNotifyRebill()
  {
    return Yii::_t($this->is_notify_rebill ? 'app.common.Yes' : 'app.common.No');
  }

  /**
   * @return string
   */
  public function getUserLink()
  {
    return Link::get(
      '/users/users/view',
      ['id' => $this->user_id],
      ['data-pjax' => 0],
      $this->user->getStringInfo(),
      false
    );
  }

  public function getStreamLink()
  {
    return Yii::$app->user->can('PromoViewOtherPeopleStreams') ? Link::get(
      '/promo/streams/view',
      ['id' => $this->stream_id],
      ['data-pjax' => 0],
      Yii::$app->formatter->asText($this->stream->name),
      false
    ) : Yii::$app->formatter->asText($this->stream->name);
  }

  public function getDomainLink()
  {
    return $this->domain_id ? Link::get(
      '/promo/domains/view',
      ['id' => $this->domain_id],
      ['data-pjax' => 0],
      Yii::$app->formatter->asText($this->domain->url)
    ) : null;
  }

  /**
   * @return string
   */
  public function getLink()
  {
    if (!$this->domain_id) return '';

    $domainUrl = trim($this->domain->url);
    $base =
      /** если нет http:// у домена, то добавляем */
      (strpos($domainUrl, 'http://') !== false || strpos($domainUrl, 'https://') !== false ? '' : 'http://') .

      Yii::$app->formatter->asText($domainUrl) .

      /** если нет слэша в конце у домена, то добавляем */
      (substr($domainUrl, -1) != '/' ? '/' : '') .

      $this->hash .
      '/';

    $queryParams = [];
    $this->subid1 && $queryParams[] = 'subid1=' . Yii::$app->formatter->asText(trim($this->subid1));
    $this->subid2 && $queryParams[] = 'subid2=' . Yii::$app->formatter->asText(trim($this->subid2));
    $this->cid_value && $queryParams[] = $this->getCidAttrName() . '=' . Yii::$app->formatter->asText(trim($this->cid_value));
    $this->trafficback_type == self::TRAFFICBACK_TYPE_DYNAMIC && $queryParams[] = 'back_url=';

    return $base . (count($queryParams) ? '?' . implode('&', $queryParams) : '');
  }

  /**
   * @return string
   */
  public function getCidAttrName()
  {
    return $this->cid ?: self::DEFAULT_CID;
  }


  /**
   * @param $format integer
   * @param $group boolean
   * @return string
   */
  public function getIPs($format = self::IP_FORMAT_RANGE, $group = false)
  {
    $operators = [];
    foreach ($this->sourceOperatorLanding as $sourceOperator) {
      $operators[$sourceOperator->operator_id] = $sourceOperator->operator->name;
    }

    $ips = [];
    $ipv6s = [];
    foreach (array_keys($operators) as $operatorId) {
      $ips[$operatorId] = OperatorIp::findAll(['operator_id' => $operatorId]);
      $ipv6s[$operatorId] = OperatorIpv6::findAll(['operator_id' => $operatorId]);
    }

    $str = '';
    foreach ($ips as $operatorId => $operatorIps) {
      $ipsStr = $this->formatIps($operatorIps, $format);
      $ipsStr .= $this->formatIpv6s($ipv6s[$operatorId]);
      $str .= $group ? $operators[$operatorId] . "\n" . $ipsStr . "\n" : $ipsStr;
    }

    return $str;
  }

  public function landingsHasRevshareCPA()
  {
    $hasCPA = $hasRevshare = false;
    /* @var $sourceOperatorLanding \mcms\promo\models\SourceOperatorLanding */
    foreach ($this->sourceOperatorLanding as $sourceOperatorLanding) {
      if (!$hasCPA) {
        $hasCPA = $sourceOperatorLanding->profit_type == SourceOperatorLanding::PROFIT_TYPE_BUYOUT;
      }

      if (!$hasRevshare) {
        $hasRevshare = $sourceOperatorLanding->profit_type == SourceOperatorLanding::PROFIT_TYPE_REBILL;
      }

      if ($hasRevshare && $hasCPA) break;
    }

    return [$hasRevshare, $hasCPA];
  }

  /**
   * TRICKY: Список стран и операторов, доступных для смарт ссылок (имеют видимые активные ленды и пришли из КП)
   * TODO: выпилить после того, как начнем получать рейтинг лендов из ML. Всем писать All
   * @return array
   */
  public function getSmartLinkCountriesOperators()
  {
    $result = [];
    $landingOperators = (new Query())
      ->select([
        'operator_id' => 'o.id',
        'operator_name' => 'o.name',
        'country_id' => 'c.id',
        'country_name' => 'c.name',
      ])
      ->from('operator_top_landings top')
      ->leftJoin(Operator::tableName() . ' o', 'o.id = top.operator_id')
      ->leftJoin(Country::tableName() . ' c', 'c.id = o.country_id')
      ->where(['top.operator_id' => AvailableOperators::getInstance(Yii::$app->user->id)->getIds()])
      ->groupBy('top.operator_id');
    foreach ($landingOperators->each() as $lo) {
      $result[$lo['country_id']]['name'] = $lo['country_name'];
      $result[$lo['country_id']]['operators'][$lo['operator_id']]['name'] = $lo['operator_name'];
    }
    return $result;
  }

  /**
   * Количество активных операторов в смарт ссылке
   * @return int
   */
  public function getSmartLinkOperatorsCount()
  {
    //TODO раскоментировать когда вернем смарт ссылки
    return 0;
//    return (int)(new Query())
//      ->from('operator_top_landings')
//      ->groupBy('operator_id')
//      ->where(['operator_id' => AvailableOperators::getInstance(Yii::$app->user->id)->getIds()])
//      ->count();
  }


  public function getListOperatorLandings()
  {
    $result = [];

    foreach ($this->sourceOperatorLanding as $operatorLanding) /* @var $operatorLanding \mcms\promo\models\SourceOperatorLanding */ {
      $result[$operatorLanding->operator->country_id]['name'] = $operatorLanding->operator->country->name;
      $result[$operatorLanding->operator->country_id]['operators'][$operatorLanding->operator_id]['name'] = $operatorLanding->operator->name;
      $result[$operatorLanding->operator->country_id]['operators'][$operatorLanding->operator_id]['landings'][$operatorLanding->landing_id] = $operatorLanding->landing;

      if ($operatorLanding->landing->isRequestStatusModeration())
        $result[$operatorLanding->operator->country_id]['operators'][$operatorLanding->operator_id]['moderation'][] = $operatorLanding->landing;

      if ($operatorLanding->landing->isRequestStatusBlocked())
        $result[$operatorLanding->operator->country_id]['operators'][$operatorLanding->operator_id]['locked'][] = $operatorLanding->landing;

      if (
        $operatorLanding->landing->isDisabled() ||
        ($operatorLanding->landing->isHidden() && !$operatorLanding->landing->getRequest()) ||
        $operatorLanding->operator->isDisabled() ||
        ($operatorLanding->landingOperator && $operatorLanding->landingOperator->is_deleted)
      ) {
        $result[$operatorLanding->operator->country_id]['operators'][$operatorLanding->operator_id]['disabled'][] = $operatorLanding->landing;
      }

      $blockedOperators = $this->getBlockedOperators()->select('id')->column();
      if (in_array($operatorLanding->operator_id, $blockedOperators) || $operatorLanding->operator->isTrafficBlocked($this->user_id))
        $result[$operatorLanding->operator->country_id]['operators'][$operatorLanding->operator_id]['blocked'] = $this;
    }
    return $result;
  }

  /**
   * Операторы лендинга выбранного в ссылке
   * @param $landingId
   * @return array
   */
  public function getSelectedOperatorsFromLanding($landingId)
  {
    return array_filter(array_map(function ($operatorLanding) use ($landingId) {
      return $operatorLanding->landing_id === $landingId ? $operatorLanding->operator_id : null;
    }, $this->sourceOperatorLanding));

  }

  /**
   * Выбран ли оператор в лендинге
   * @param integer $operatorId
   * @param integer $landingId
   * @return bool
   */
  public function isSelectedOperatorForLanding($operatorId, $landingId)
  {
    return in_array($operatorId, $this->getSelectedOperatorsFromLanding($landingId), true);
  }

  /**
   * Выбран ли хотя бы один операторв в лендинге
   * @param array $operators
   * @param integer $landingId
   * @return bool
   */
  public function isNoOperatorSelected($operators, $landingId)
  {
    $isNoSelectedOperators = true;
    foreach ($this->sourceOperatorLanding as $sourceOperatorLanding) {
      foreach ($operators as $operator) {
        if ($operator->id === $sourceOperatorLanding->operator_id
          && $landingId === $sourceOperatorLanding->landing_id) {
          $isNoSelectedOperators = false;
          break;
        }
      }
    }
    return $isNoSelectedOperators;
  }

  public function getLandingsOnModeration()
  {
    return array_filter(array_map(function ($operatorLanding) {
      return $operatorLanding->landing->isRequestStatusModeration()
        ? $operatorLanding->landing->id . '. ' . $operatorLanding->landing->name
        : false;
    }, $this->sourceOperatorLanding));
  }

  public function getLandingsLocked()
  {
    return array_filter(array_map(function ($operatorLanding) {
      return $operatorLanding->landing->isRequestStatusBlocked()
        ? $operatorLanding->landing->id . '. ' . $operatorLanding->landing->name
        : false;
    }, $this->sourceOperatorLanding));
  }

  public function getDisabledLandings()
  {
    return array_filter(array_map(function ($operatorLanding) {
      return $operatorLanding->landing->isDisabled()
        ? $operatorLanding->landing->id . '. ' . $operatorLanding->landing->name
        : false;
    }, $this->sourceOperatorLanding));
  }

  public function getBlockedOperatorsList()
  {
    return array_unique(array_filter(array_map(function ($operatorLanding) {
      $blockedOperators = $this->getBlockedOperators()->select('id')->column();
      return (in_array($operatorLanding->operator_id, $blockedOperators))
        ? $operatorLanding->operator->name
        : false;
    }, $this->sourceOperatorLanding)));
  }

  public function getOperatorLandingLinks()
  {
    $result = [];
    foreach ($this->sourceOperatorLanding as $operatorLanding) {
      /* @var $operatorLanding \mcms\promo\models\SourceOperatorLanding */

      $style = '';
      if (
        $operatorLanding->landing->status == Landing::STATUS_INACTIVE ||
        $operatorLanding->operator->isTrafficBlocked($this->user_id) ||
        $operatorLanding->operator->isDisabled($this->user_id)
      ) {
        $style = 'color : red';
      } else if (
      !$operatorLanding->landing->isNormal() &&
      !$operatorLanding->landing->getLandingUnblockRequest()
        ->andWhere(['user_id' => $this->user_id, 'status' => LandingUnblockRequest::STATUS_UNLOCKED])->exists()
      ) {
        $style = 'color : #c09853';
      }

      $result[] = Modal::widget([
        'toggleButtonOptions' => [
          'tag' => 'a',
          'label' => $operatorLanding->landing->name,
          'style' => $style,
          'data-pjax' => 0,
        ],
        'size' => Modal::SIZE_LG,
        'url' => Url::to(['/promo/landings/view-modal/', 'id' => $operatorLanding->landing->id]),
      ]);
    }
    return implode(', ', $result);
  }

  public static function getOperatorTooltip($operator, $title, $label)
  {
    return Html::tag('li', $operator['name'] . ' ' .
      Html::tag('span',
        Html::tag('i', count($operator['landings'])), [
          'data-toggle' => 'tooltip',
          'data-placement' => 'top',
          'data-pjax' => 0,
          'title' => $title
        ]), ['class' => $label]);
  }

  private function formatIps($ips, $formatType)
  {
    $str = '';
    foreach ($ips as $ip) {
      $cidr = $ip->from_ip . '/' . $ip->mask;
      $str .= $formatType == self::IP_FORMAT_CIDR ? $cidr : implode(' - ', CIDR::cidrToRange($cidr));
      $str .= "\n";
    }
    return $str;
  }

  /**
   * @param OperatorIpv6[] $ips
   * @return string
   */
  private function formatIpv6s($ips)
  {
    $str = '';
    foreach ($ips as $ip) {
      $str .= $ip->ip . '/' . $ip->mask;
      $str .= "\n";
    }
    return $str;
  }

  public function load($data, $formName = null)
  {
    if (!parent::load($data, $formName)) return false;
    if (in_array('landingModels', $this->activeAttributes())) $this->loadLandings($data);
    return true;
  }

  /**
   * @param $data
   * @return bool
   */
  private function loadLandings($data)
  {
    $this->landingModels = [];
    foreach (ArrayHelper::getValue($data, 'SourceOperatorLanding', []) as $key => $landing) {
      $landingModel = SourceOperatorLanding::findOrCreateModel(
        $this->id,
        ArrayHelper::getValue($landing, 'operator_id'),
        ArrayHelper::getValue($landing, 'landing_id')
      );
      $landingModel->setAttributes($landing);
      $this->landingModels[$key] = $landingModel;
    }
  }

  /**
   * @param $insert
   * @throws Exception
   */
  protected function saveOrDeleteLandings($insert)
  {
    if ($this->landingModels === null || $this->isLandingsConvertTest()) return;

    $transaction = Yii::$app->db->beginTransaction();
    try {
      $oldIDs = [];
      if (!$insert) {
        foreach ($this->sourceOperatorLanding as $landing) {
          $oldIDs['operator' . $landing->operator->id . 'landing' . $landing->landing->id][$landing->operator->id] = $landing->landing->id;
        }
      }

      $newIDs = [];
      foreach ($this->landingModels as $landing) {
        $newIDs['operator' . $landing->operator->id . 'landing' . $landing->landing->id][$landing->operator->id] = $landing->landing->id;
      }

      foreach ($this->landingModels as $landingModel) {
        $landingModel->source_id = $this->id;
        if (!$landingModel->save(false)) throw new ModelNotSavedException;
      }

      $deletedIDs = array_diff_key($oldIDs, $newIDs);

      if (!empty($deletedIDs)) {
        foreach ($deletedIDs as $id) {
          foreach ($id as $operator_id => $landing_id) {
            SourceOperatorLanding::deleteAll(['landing_id' => $landing_id, 'operator_id' => $operator_id, 'source_id' => $this->id]);
          }
        }
      }
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollBack();
      throw $e;
    }
  }

  public static function getViewUrl($id, $sourceType = null, $asString = false)
  {
    if (is_null($sourceType)) return null;

    switch ($sourceType) {
      case self::SOURCE_TYPE_LINK:
        $controller = 'arbitrary-sources';
        break;
      case self::SOURCE_TYPE_WEBMASTER_SITE:
        $controller = 'webmaster-sources';
        break;
      case self::SOURCE_TYPE_SMART_LINK:
        $controller = 'smart-links';
        break;
    }

    $arr = ['/promo/' . $controller . '/view', 'id' => $id];
    return $asString ? Url::to($arr) : $arr;
  }

  /**
   * Получение списка операторов для которых включены преленды
   * @param $glue string
   * @return string
   */
  public function getAddPrelandOperatorNames($glue = '<br />')
  {
    $result = [];
    foreach ($this->addPrelandOperators as $operator) {
      $result[] = $operator->getViewLink();
    }
    return implode($glue, $result);
  }

  /**
   * Получение id операторов для которых включены преленды
   * @return array
   */
  public function getAddPrelandOperatorIds()
  {
    $result = [];
    foreach ($this->addPrelandOperators as $operator) {
      $result[] = $operator->id;
    }
    return $result;
  }

  /**
   * Получение списка операторов для которых отключены преленды
   * @param $glue string
   * @return string
   */
  public function getOffPrelandOperatorNames($glue = '<br />')
  {
    $result = [];
    foreach ($this->offPrelandOperators as $operator) {
      $result[] = $operator->getViewLink();
    }
    return implode($glue, $result);
  }

  /**
   * Получение списка заблокированных операторов
   * @param $glue string
   * @return string
   */
  public function getBlockedOperatorNames($glue = '<br />')
  {
    $result = [];
    foreach ($this->blockedOperators as $operator) {
      $result[] = $operator->getViewLink();
    }
    return implode($glue, $result);
  }

  /**
   * Получение выбранного оператора из ссылки
   * Сортирока идет по странам по ид, затем по операторам
   * @return Operator
   */
  public function getActiveOperator()
  {
    $operator = null;
    $countryId = null;
    foreach ($this->sourceOperatorLanding as $sourceOperatorLanding) {
      if (!$sourceOperatorLanding->operator->isActive() || $sourceOperatorLanding->operator->isTrafficBlocked() || !$sourceOperatorLanding->operator->country->isActive()) {
        continue;
      }

      if ($sourceOperatorLanding->landing->access_type == Landing::ACCESS_TYPE_HIDDEN && is_null($sourceOperatorLanding->landing->getRequest())) {
        continue;
      }

      if ($operator === null
        || $sourceOperatorLanding->operator->country_id < $countryId
        || $sourceOperatorLanding->operator->country_id == $countryId && $sourceOperatorLanding->operator->id < $operator->id) {
        $operator = $sourceOperatorLanding->operator;
        $countryId = $operator->country_id;
      }
    }
    return $operator;
  }

  /**
   * Получение массива с количеством выбранных лендингов, сгруппированным по странам и операторам
   * @return array
   */
  public function getSelectedCount()
  {
    $landingsSelectedCount = [];
    foreach ($this->sourceOperatorLanding as $landing) {

      $countryId = $landing->operator->country_id;
      $operatorId = $landing->operator_id;

      if (!isset($landingsSelectedCount[$countryId])) {
        $landingsSelectedCount[$countryId] = [];
      }

      if ($landing->landing->access_type == Landing::ACCESS_TYPE_HIDDEN &&
        is_null($landing->landing->getRequest())) continue;

      if (!isset($landingsSelectedCount[$countryId][$operatorId])) {
        $landingsSelectedCount[$countryId][$operatorId] = 0;
      }

      $landingsSelectedCount[$countryId][$operatorId]++;
    }

    return $landingsSelectedCount;
  }

  /**
   * По объекту Source возвращает отформатированную строку вида:
   * "#35 - Source name"
   *
   * @return string
   */
  public function getStringInfo()
  {
    return sprintf(
      '#%s - %s',
      ArrayHelper::getValue($this, 'id'),
      Yii::$app->formatter->asText(ArrayHelper::getValue($this, 'name'))
    );
  }

  public function getSourceOperatorlandingsDataProvider()
  {
    $sourceOperatorLandings = [];
    foreach ($this->sourceOperatorLanding as $sourceOperatorLanding) {
      $landingOperator = Yii::$app->getModule('promo')->api('landingOperatorById', [
        'landingId' => $sourceOperatorLanding->landing_id,
        'operatorId' => $sourceOperatorLanding->operator_id,
      ])->getResult();
      if (!$landingOperator) continue;
      $profitTypeName = $sourceOperatorLanding->getProfitTypeName();
      $landing = $sourceOperatorLanding->landing;
      $sourceOperatorLanding = ArrayHelper::toArray($sourceOperatorLanding);
      $sourceOperatorLanding['profitTypeName'] = $profitTypeName;
      $sourceOperatorLandings[] = ArrayHelper::merge(
        $sourceOperatorLanding,
        ['landingOperator' => $landingOperator],
        ['landing' => $landing]
      );
    }

    return new ArrayDataProvider(['allModels' => $sourceOperatorLandings]);
  }

  /**
   * Если вебмастер поменял CPA на Ребилл, надо поменять profit_type у лендов в связке
   * @param boolean $insert
   * @param array $changedAttributes
   */
  protected function updateProfitType($insert, $changedAttributes)
  {
    if ($insert) return;

    if ($this->scenario != self::SCENARIO_PARTNER_UPDATE_WEBMASTER_SOURCE) return;

    if (in_array('default_profit_type', $changedAttributes)) return;

    // обновляем
    SourceOperatorLanding::updateAll(
      ['profit_type' => $this->default_profit_type],
      ['source_id' => $this->id]
    );
  }

  public function getDeletedByUserName()
  {
    if (!$this->deleted_by) {
      return null;
    }
    $user = Yii::$app->getModule('users')->api('getOneUser', ['user_id' => $this->deleted_by])->getResult();
    return $user ? Yii::$app->formatter->asText($user->username) : null;
  }

  /**
   * @return array
   */
  public static function getAdsTypes()
  {
    return AdsType::getDropDown();
  }

  /**
   * @param bool|null $isActive
   * @return \yii\db\ActiveQuery
   */
  public function getBlockedOperators($isActive = null)
  {
    $query = $this->hasMany(Operator::class, ['id' => 'operator_id'])
      ->viaTable(static::LINK_BLOCKED_OPERATORS_TABLE, ['source_id' => 'id']);

    if ($isActive !== null) {
      $query->andWhere(['is_disabled' => $isActive ? 0 : 1]);
    }

    return $query;
  }

  /**
   * @param bool|null $isActive
   * @return \yii\db\ActiveQuery
   */
  public function getAddPrelandOperators($isActive = null)
  {
    $query = $this->hasMany(Operator::class, ['id' => 'operator_id'])
      ->viaTable(static::LINK_ADD_PRELAND_OPERATORS_TABLE, ['source_id' => 'id']);

    if ($isActive !== null) {
      $query->andWhere(['is_disabled' => $isActive ? 0 : 1]);
    }

    return $query;
  }

  /**
   * @param bool|null $isActive
   * @return \yii\db\ActiveQuery
   */
  public function getOffPrelandOperators($isActive = null)
  {
    $query = $this->hasMany(Operator::class, ['id' => 'operator_id'])
      ->viaTable(static::LINK_OFF_PRELAND_OPERATORS_TABLE, ['source_id' => 'id']);

    if ($isActive !== null) {
      $query->andWhere(['is_disabled' => $isActive ? 0 : 1]);
    }

    return $query;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public static function getInactiveSourcesQuery()
  {
    return static::find()
      ->where(['<>', 'status', self::STATUS_APPROVED]);
  }

  /**
   * @return bool
   */
  public function allowAllUrl()
  {
    return !!$this->allow_all_url;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLandingSet()
  {
    return $this->hasOne(LandingSet::class, ['id' => 'set_id']);
  }

  /**
   * Получить источники по категории
   *
   * @param int|null $categoryId
   * @param int|null $ignore_set_id
   * @param bool $returnEmptyCategory
   * @return array|\yii\db\ActiveRecord[]
   */
  public static function getByCategory($categoryId = null, $ignore_set_id = null, $returnEmptyCategory = true)
  {
    $sources = self::find()->where(['source_type' => Source::SOURCE_TYPE_WEBMASTER_SITE]);
    if (!is_null($categoryId)) {
      if ($returnEmptyCategory) {
        $sources->andWhere(['OR', ['category_id' => $categoryId], ['category_id' => null]]);
      } else {
        $sources->andWhere(['category_id' => $categoryId]);
      }
    }
    if (!is_null($ignore_set_id)) {
      $sources->andWhere(['OR', ['!=', 'set_id', $ignore_set_id], ['set_id' => null]]);
    }

    return $sources->all();
  }

  /**
   * Роут для изменения записи
   */
  public function getUpdateRoute()
  {
    $controller = $this->source_type == self::SOURCE_TYPE_WEBMASTER_SITE ? 'webmaster-sources' : 'arbitrary-sources';
    return ['/promo/' . $controller . '/update/', 'id' => $this->id];
  }

  /**
   * @return bool
   */
  public function canManageForceOperatorOption()
  {
    return Yii::$app->user->can('PromoManageForceOperatorOption');
  }

  /**
   * @return bool
   */
  public function canManageTrafficFiltersOff()
  {
    return Yii::$app->user->can('PromoManageSourceTrafficFiltersOff');
  }

  /**
   * Существует ли глобальный постбек URL у партнера
   * @param $attribute
   */
  public function isGlobalPostbackUrlExist($attribute)
  {
    if ($this->$attribute && !UserPromoSetting::getGlobalPostbackUrl($this->user_id)) {
      $this->addError($attribute, Yii::_t('promo.sources.global-postback-url-not-defined'));
    }
  }

  /**
   * Существует ли глобальный постбек URL для жалоб у партнера
   * @param $attribute
   */
  public function isGlobalComplainsPostbackUrlExist($attribute)
  {
    if ($this->$attribute && !UserPromoSetting::getGlobalComplainsPostbackUrl($this->user_id)) {
      $this->addError($attribute, Yii::_t('promo.sources.global-complains-postback-url-not-defined'));
    }
  }

  /**
   * Ссылка на просмотр источника/ссылки
   * @return string
   */
  public function getViewLink()
  {
    switch ($this->source_type) {
      case self::SOURCE_TYPE_LINK:
        $controller = 'arbitrary-sources';
        break;
      case self::SOURCE_TYPE_WEBMASTER_SITE:
        $controller = 'webmaster-sources';
        break;
      case self::SOURCE_TYPE_SMART_LINK:
        $controller = 'smart-links';
        break;
    }

    return Link::get(
      '/promo/' . $controller . '/view',
      ['id' => $this->id],
      ['data-pjax' => 0],
      $this->getStringInfo()
    );
  }

  /**
   * Правила прелендов подходящие для текущего источника
   * @param $type bool|array тип преленда по умолчанию
   * @return \yii\db\ActiveQuery
   */
  protected function findDefaultPrelandOperators($type)
  {
    return PrelandDefaults::find()
      ->where([
        'or', ['user_id' => $this->user_id], ['user_id' => null]
      ])
      ->andWhere([
        'or', ['stream_id' => $this->stream_id], ['stream_id' => null]
      ])
      ->andWhere([
        'or', ['source_id' => $this->id], ['source_id' => null]
      ])
      ->andWhere(['status' => PrelandDefaults::STATUS_ACTIVE, 'type' => $type])
      ->orderBy(['user_id' => SORT_DESC]);
  }

  /**
   * Датапровайдер для грида прелендов
   * @return ActiveDataProvider
   */
  public function getPrelandOperators()
  {
    return new ActiveDataProvider([
      'query' => $this->findDefaultPrelandOperators([PrelandDefaults::TYPE_ADD, PrelandDefaults::TYPE_OFF]),
    ]);
  }

  /**
   * Является ли источник смарт-ссылкой
   * @return bool
   */
  public function isSmartLink()
  {
    return $this->source_type === self::SOURCE_TYPE_SMART_LINK;
  }
}