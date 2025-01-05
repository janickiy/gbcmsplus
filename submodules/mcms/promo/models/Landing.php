<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Link;
use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\components\AvailableOperators;
use mcms\promo\components\events\LandingCreatedReseller;
use mcms\promo\components\landing_sets\LandingSetsLandsUpdater;
use mcms\promo\components\LandingOperatorPrices;
use mcms\promo\models\search\LandingSetSearch;
use mcms\promo\Module;
use Yii;
use mcms\promo\components\events\LandingCreated;
use mcms\promo\components\events\LandingUpdated;
use mcms\user\models\User;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\db\Query;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\web\UploadedFile;
use yii\console\Application as ConsoleApplication;
use yii\helpers\Url;
use yiidreamteam\upload\FileUploadBehavior;

/**
 * This is the model class for table "{{%landings}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $service_url
 * @property integer $category_id
 * @property integer $offer_category_id
 * @property integer $provider_id
 * @property string $image_src
 * @property integer $access_type
 * @property integer $status
 * @property string $description
 * @property string $custom_url
 * @property string $comment
 * @property integer $rating
 * @property integer $auto_rating
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $sync_updated_at
 * @property integer $allow_sync_buyout_prices
 * @property integer $allow_sync_status
 * @property integer $allow_sync_access_type
 * @property integer $to_landing_id
 * @property integer $send_id
 * @property string $operators_text
 * @property integer $rebill_period
 * @property string $promo_materials
 * @property string $promo_materials_file_src
 * @property string $gridOperators
 *
 * @property LandingOperator[] $landingOperator
 * @property Operator[] $operators
 * @property @deprecated Operator[] $operator
 * @property LandingUnblockRequest[] $landingUnblockRequest
 * @property LandingUnblockRequest $landingUnblockRequestCurrentUser
 * @property LandingOperator[] $activeLandingOperators
 *
 * @property OfferCategory $offerCategory
 * @property LandingCategory $category
 * @property User $createdBy
 * @property Provider $provider
 * @property PersonalProfit[] $personalProfits
 * @property SourceOperatorLanding[] $sourceOperatorLanding
 * @property VisibleLandingPartner[] $visibleLandingPartners
 * @property User[] $users
 */
class Landing extends \yii\db\ActiveRecord
{

  const ACCESS_TYPE_HIDDEN = 0;
  const ACCESS_TYPE_NORMAL = 1;
  const ACCESS_TYPE_BY_REQUEST = 2;

  const STATUS_INACTIVE = 0;
  const STATUS_ACTIVE = 1;
  const STATUS_BLOCKED = 2;

  const AUTO_RATING_DISABLED = 0;
  const AUTO_RATING_ENABLED = 1;

  const UPLOAD_DIR = '/promo/landing/';

  const LANDINGS_BY_CATEGORY_ACTIVE_CACHE_KEY = 'landings_by_category_active';
  const LANDINGS_BY_CATEGORY_ALL_CACHE_KEY = 'landings_by_category_all';
  const LANDINGS_BY_CATEGORY_CACHE_TAGS = ['landing', 'category'];

  const TRAFFIC_TYPE_UNKNOWN = 0;
  const TRAFFIC_TYPE_REVSHARE = 1;
  const TRAFFIC_TYPE_CPA = 2;
  const TRAFFIC_TYPE_ONETIME = 3;

  public $uploadPath;
  public $uploadUrl;
  public $code;

  /** @var  UploadedFile */
  public $imageFile; // аттрибут для input=file
  public $imageFileName; // хранит имя файла (без пути)

  /** @var  UploadedFile */
  public $promoMaterialsFile;
  public $promoMaterialsFileName;

  /**
   * @var LandingOperator[]
   */
  public $operatorModels;
  /**
   * @var LandingUnblockRequest для событий [[LandingUnlocked]] и [[LandingDisabled]]
   */
  public $unblockRequest;

  /**
   * @var array заполняется через select2
   */
  public $platformIds;

  /**
   * @var array заполняется через select2
   */
  public $forbiddenTrafficTypeIds;

  const SCENARIO_SYNC = 'sync_mobleaders';
  const SCENARIO_CHANGE_STATUS = 'change_status';
  const SCENARIO_CREATE_WITH_EXTERNAL_PROVIDER = 'create_with_external_provider';
  const SCENARIO_COPY = 'copy';

  /**
   * @inheritDoc
   */
  public function init()
  {
    parent::init();
    $date = date('Ymd');
    $this->uploadPath = Yii::getAlias('@uploadPath') . self::UPLOAD_DIR . $date . DIRECTORY_SEPARATOR;
    $this->uploadUrl = Yii::getAlias('@uploadUrl') . self::UPLOAD_DIR . $date . '/';
  }

  /**
   * @return array
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%landings}}';
  }

  /**
   * @return array
   */
  public function getStatusColors()
  {
    $statuses = [
      self::STATUS_ACTIVE => '',
      self::STATUS_INACTIVE => 'danger',
      self::STATUS_BLOCKED => 'danger',
    ];
    return ArrayHelper::getValue($statuses, $this->status, '');
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['created_by', 'default', 'value' => isset(Yii::$app->user) ? Yii::$app->user->id : null],
      ['imageFile', 'checkEmptyImage', 'skipOnEmpty' => false],
      ['imageFile', 'file', 'extensions' => ['jpg', 'jpeg', 'png', 'gif']],
      ['promoMaterialsFile', 'file'],

      ['rating', 'default', 'value' => 1],
      [['allow_sync_buyout_prices', 'allow_sync_status', 'allow_sync_access_type'], 'default', 'value' => 1],
      [['image_src', 'platformIds', 'forbiddenTrafficTypeIds', 'send_id', 'promo_materials_file_src'], 'safe'],
      [['name', 'offer_category_id', 'category_id', 'provider_id', 'created_by', 'status', 'access_type'], 'required'],
      [['category_id', 'offer_category_id', 'provider_id', 'access_type', 'status', 'rating', 'auto_rating', 'created_by', 'allow_sync_buyout_prices'], 'integer'],
      [['description', 'name', 'comment'], 'string'],
      [['send_id'], 'string', 'max' => 64],
      [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => LandingCategory::class, 'targetAttribute' => ['category_id' => 'id']],
      [['offer_category_id'], 'default', 'value' => OfferCategory::DEFAULT_CATEGORY_ID],
      [['offer_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => OfferCategory::class, 'targetAttribute' => ['offer_category_id' => 'id']],
      [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
      [['provider_id'], 'exist', 'skipOnError' => true, 'targetClass' => Provider::class, 'targetAttribute' => ['provider_id' => 'id']],
      [['sync_updated_at'], 'safe'],
      [['operators_text'], 'string'],
      [['service_url'], 'filter', 'filter' => 'trim'],
//      [['service_url'], 'url'], // решили убрать валидацию, иначе с МЛ не все ленды добавляются, а поле чисто для инфы
      [['status', 'access_type'], 'filter', 'filter' => 'intval'],
      [['custom_url'], 'string', 'max' => 255],
      [['custom_url'], 'url',],
      [
        'status',
        function ($attribute) {
          if (!$this->canEnable() && !$this->isNewRecord) {
            $this->addError($attribute, Yii::_t('promo.landings.status-changing-forbidden'));
          }
        },
        'when' => function () {
          return $this->isAttributeChanged('status') &&
            $this->getOldAttribute('status') !== self::STATUS_ACTIVE;
        },
        'except' => [self::SCENARIO_SYNC]
      ],
      [
        'status',
        function ($attribute) {
          if (!$this->allow_sync_status && !$this->isNewRecord) {
            $this->addError($attribute, Yii::_t('promo.landings.status-changing-forbidden'));
          }
        },
        'when' => function () {
          return $this->isAttributeChanged('status') &&
            $this->getOldAttribute('status') !== self::STATUS_ACTIVE;
        },
        'on' => [self::SCENARIO_SYNC]
      ]
    ];
  }

  public function scenarios()
  {
    $allFields = array_keys($this->getAttributes());
    // Эти поля устанавливаются только вручную
    $syncFields = array_diff($allFields, ['allow_sync_status', 'allow_sync_access_type']);

    return array_merge(
      parent::scenarios(),
      [
        self::SCENARIO_SYNC => $syncFields,
        self::SCENARIO_CREATE_WITH_EXTERNAL_PROVIDER => array_merge($allFields, ['platformIds', 'forbiddenTrafficTypeIds',]),
        self::SCENARIO_CHANGE_STATUS => ['status'],
        self::SCENARIO_COPY => $allFields,
      ]
    );
  }

  /**
   * Если сменили access_type или status, обнуляем allow_sync_access_type или allow_sync_status соответственно
   * Для того, чтобы значения не перезатирались при синке
   * @inheritdoc
   */
  public function beforeSave($insert)
  {
    if ($this->scenario === self::SCENARIO_SYNC) {
      return parent::beforeSave($insert);
    }

    // TRICKY: Сделано для того, чтобы при создании заблокированного ленда не запретилось изменение статуса
    // allow_sync_access_type и allow_sync_buyout_prices добавил на всякий случай
    // синк лендов от внешнего провайдера все равно не происходит
    if ($this->scenario === self::SCENARIO_CREATE_WITH_EXTERNAL_PROVIDER) {
      $this->allow_sync_status = 0;
      $this->allow_sync_access_type = 0;
      $this->allow_sync_buyout_prices = 0;
    }

    if ($insert) {
      return parent::beforeSave($insert);
    }

    if ($this->isAttributeChanged('access_type')) {
      $this->disallowSyncAccessType();
    }
    if ($this->isAttributeChanged('status')) {
      $this->disallowSyncStatus();
    }

    return parent::beforeSave($insert);
  }

  /**
   * Можно ли включать лендинг
   * TRICKY: Можно включать только, если лендинг выключен не через синк (в этом случае allow_sync_status = 0)
   * @return bool
   */
  public function canEnable()
  {
    return !(bool)$this->allow_sync_status;
  }

  /**
   * Можно ли менять статус лендинга
   * @return bool
   */
  public function canChangeStatus()
  {
    return $this->isEnabled() || $this->canEnable();
  }

  /**
   * Запрещаем синк статуса
   */
  protected function disallowSyncStatus()
  {
    $this->allow_sync_status = 0;
  }

  /**
   * Запрещаем синк типа доступа
   */
  protected function disallowSyncAccessType()
  {
    $this->allow_sync_access_type = 0;
  }

  /**
   * Кастомное правило валидации для файлов.
   * Делает поле ФАЙЛ обязательным для заполнения только при создании записи.
   * При обновлении записи - если новый файл не приложен к форме, то валидация пройдёт успешно,
   * это значит что катинку не поменяли при обновлении.
   *
   * @param $attribute
   * @param $params
   * @return bool
   */
  public function checkEmptyImage($attribute, $params)
  {

    if ($this->scenario == self::SCENARIO_SYNC) return true;

    if (
      $this->isNewRecord && !$this->imageFile && ! Yii::$app instanceof ConsoleApplication ||
      !$this->isNewRecord && !$this->imageFile && !$this->image_src
    ) {
      $this->addError($attribute, Yii::_t('promo.landings.attribute-image_src_required'));
      return false;
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'name' => Yii::_t('promo.landings.attribute-name'),
      'code' => Yii::_t('promo.landings.attribute-code'),
      'category_id' => Yii::_t('promo.landings.attribute-category_id'),
      'offer_category_id' => Yii::_t('promo.landings.attribute-offer_category_id'),
      'provider_id' => Yii::_t('promo.landings.attribute-provider_id'),
      'image_src' => Yii::_t('promo.landings.attribute-image_src'),
      'access_type' => Yii::_t('promo.landings.attribute-access_type'),
      'status' => Yii::_t('promo.landings.attribute-status'),
      'description' => Yii::_t('promo.landings.attribute-description'),
      'custom_url' => Yii::_t('promo.landings.attribute-custom_url'),
      'comment' => Yii::_t('promo.landings.attribute-comment'),
      'rating' => Yii::_t('promo.landings.attribute-rating'),
      'rebill_period' => Yii::_t('promo.landings.attribute-rebill_period'),
      'auto_rating' => Yii::_t('promo.landings.attribute-auto_rating'),
      'allow_sync_buyout_prices' => Yii::_t('promo.landings.attribute-allow_sync_buyout_prices'),
      'allow_sync_status' => Yii::_t('promo.landings.attribute-allow_sync_status'),
      'allow_sync_access_type' => Yii::_t('promo.landings.attribute-allow_sync_access_type'),
      'created_by' => Yii::_t('promo.landings.attribute-created_by'),
      'created_at' => Yii::_t('promo.landings.attribute-created_at'),
      'updated_at' => Yii::_t('promo.landings.attribute-updated_at'),
      'platformIds' => Yii::_t('promo.landings.attribute-platformIds'),
      'forbiddenTrafficTypeIds' => Yii::_t('promo.landings.attribute-forbiddenTrafficTypeIds'),
      'send_id' => Yii::_t('promo.landings.attribute-send_id'),
      'operators' => Yii::_t('promo.landings.operator-list'),
      'countries' => Yii::_t('promo.landings.country-list'),
      'operatorModels' => Yii::_t('promo.landings.operatorModels'),
      'operators_text' => Yii::_t('promo.landings.attribute-operators_text'),
      'promo_materials' => Yii::_t('promo.landings.attribute-promo_materials'),
      'promo_materials_file_src' => Yii::_t('promo.landings.attribute-promo_materials_file_src'),
    ];
  }


  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLandingOperator()
  {
    return $this->hasMany(LandingOperator::class, ['landing_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   * @deprecated
   */
  public function getOperator()
  {
    return $this->hasMany(Operator::class, ['id' => 'operator_id'])->viaTable('{{%landing_operators}}', ['landing_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getOperators()
  {
    return $this->getOperator();
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSets()
  {
    return $this->hasMany(LandingSet::class, ['id' => 'set_id'])->viaTable(LandingSetItem::tableName(), ['landing_id' => 'id']);
  }

  /**
   * @return string
   */
  public function getSetsLabel()
  {
    return ($count = $this->getSets()->count())
      ? Link::get(
        '/promo/landing-sets/index/',
        [(new LandingSetSearch())->formName() . '[landing_id]' => $this->id],
        ['data-pjax' => 0],
        Yii::_t('promo.landings.sets')) . ' ' . Html::tag('span', $count, ['class' => 'label label-default'])
      : Yii::_t('promo.landings.sets') . ' ' . Html::tag('span', $count, ['class' => 'label label-default']);
  }

  public function getOperatorNames()
  {
    $operators = [];
    foreach($this->operator as $operator) {
      $operators[] = $operator->name;
    }
    return implode(', ', $operators);
  }

  public function getOperatorsTextOrNames()
  {
    if ($this->operators_text) {
      return $this->operators_text;
    }

    return $this->getOperatorNames();
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getPlatforms()
  {
    return $this->hasMany(Platform::class, ['id' => 'platform_id'])->viaTable('{{%landing_platforms}}', ['landing_id' => 'id']);
  }

  public function getLandingPlatforms()
  {
    return $this->hasMany(LandingPlatform::class, ['landing_id' => 'id']);
  }

  public function getPlatformsNameText()
  {
    $platforms = [];
    foreach($this->landingPlatforms as $platform) {
      $platforms[] = $platform->platform->name;
    }
    return implode(', ', $platforms);
  }


  /**
   * @return \yii\db\ActiveQuery
   */
  public function getForbiddenTrafficTypes()
  {
    return $this->hasMany(TrafficType::class, ['id' => 'forbidden_traffic_type_id'])->viaTable('{{%landing_forbidden_traffic_types}}', ['landing_id' => 'id']);
  }

  public function getLandingForbiddenTrafficTypes()
  {
    return $this->hasMany(LandingForbiddenTrafficType::class, ['landing_id' => 'id']);
  }

  public function getForbiddenTrafficTypesNames()
  {
    $trafficTypes = [];
    foreach($this->forbiddenTrafficTypes as $trafficType) {
      $trafficTypes[] = $trafficType->name;
    }
    return implode(', ', $trafficTypes);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLandingUnblockRequest()
  {
    return $this->hasMany(LandingUnblockRequest::class, ['landing_id' => 'id']);
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
  public function getOfferCategory()
  {
    return $this->hasOne(OfferCategory::class, ['id' => 'offer_category_id']);
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
  public function getProvider()
  {
    return $this->hasOne(Provider::class, ['id' => 'provider_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getPersonalProfits()
  {
    return $this->hasMany(PersonalProfit::class, ['landing_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSourceOperatorLanding()
  {
    return $this->hasMany(SourceOperatorLanding::class, ['landing_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getVisibleLandingPartners()
  {
    return $this->hasMany(VisibleLandingPartner::class, ['landing_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUsers()
  {
    return $this->hasMany(User::class, ['id' => 'user_id'])->viaTable('{{%visible_landings_partners}}', ['landing_id' => 'id']);
  }

  /**
   * @param null $status
   * @return array|mixed
   */
  public function getStatuses($status = null)
  {
    $list = [
      self::STATUS_ACTIVE => Yii::_t('promo.landings.status-active'),
      self::STATUS_INACTIVE => Yii::_t('promo.landings.status-inactive'),
      self::STATUS_BLOCKED => Yii::_t('promo.landings.status-blocked'),
    ];

    if (!Yii::$app->user->can(Module::PERMISSION_CAN_VIEW_BLOCKED_LANDINGS)) {
      unset($list[self::STATUS_BLOCKED]);
    }

    return isset($status) ? ArrayHelper::getValue($list, $status, null) : $list;
  }


  /**
   * @return array|mixed
   */
  public function getCurrentStatusName()
  {
    return $this->getStatuses($this->status);
  }

  /**
   * @param null $type
   * @return array|mixed
   */
  public static function getAccessTypes($type = null)
  {
    $list = [
      self::ACCESS_TYPE_NORMAL => Yii::_t('promo.landings.access_type-normal'),
      self::ACCESS_TYPE_BY_REQUEST => Yii::_t('promo.landings.access_type-by_request'),
      self::ACCESS_TYPE_HIDDEN => Yii::_t('promo.landings.access_type-hidden'),
    ];
    return isset($type) ? ArrayHelper::getValue($list, $type, null) : $list;
  }

  /**
   * @return array|mixed
   */
  public function getCurrentAccessTypeName()
  {
    return $this->getAccessTypes($this->access_type);
  }

  /**
   * @param null $type
   * @return array|mixed
   */
  public function getAutoRatingTypes($type = null)
  {
    $list = [
      self::AUTO_RATING_DISABLED => Yii::_t('promo.landings.auto_rating-disabled'),
      self::AUTO_RATING_ENABLED => Yii::_t('promo.landings.auto_rating-enabled'),
    ];
    return isset($type) ? ArrayHelper::getValue($list, $type, null) : $list;
  }


  /**
   * @return array|mixed
   */
  public function getCurrentAutoRatingTypeName()
  {
    return $this->getAutoRatingTypes($this->auto_rating);
  }

  /**
   * @param bool|true $activeOnly
   * @return array
   */
  public function getCategories($activeOnly = true)
  {
    return LandingCategory::getAllMap($activeOnly);
  }

  /**
   * @param bool|true $activeOnly
   * @return array
   */
  public function getOfferCategories($activeOnly = true)
  {
    return OfferCategory::getDropdownItems($activeOnly);
  }

  /**
   * @return string
   */
  public function getOfferCategoryLink()
  {
    return $this->offerCategory->getViewLink();
  }

  /**
   * @return string
   */
  public function getCategoryLink()
  {
    return $this->category->getViewLink();
  }

  /**
   * @return string
   */
  public function getProviderLink()
  {
    return $this->provider->getViewLink();
  }

  /**
   * @param bool|true $activeOnly
   * @return array
   */
  public function getProviders($activeOnly = true)
  {
    $providers = Provider::find()->orderBy('name');
    if ($activeOnly) $providers->where('status = :active_status', [':active_status' => Provider::STATUS_ACTIVE]);
    return ArrayHelper::map($providers->each(), 'id', 'name');
  }

  /**
   * @return array
   */
  public function getRatings()
  {
    return range(1, 10, 1);
  }

  /**
   * @param array $data
   * @param null $formName
   * @return bool
   */
  public function load($data, $formName = null)
  {
    if (!parent::load($data, $formName)) return false;
    $this->imageFile = UploadedFile::getInstance($this, 'imageFile');
    $this->promoMaterialsFile = UploadedFile::getInstance($this, 'promoMaterialsFile');

    if ($this->imageFile) {
      $this->imageFileName = $this->generateFileName($this->imageFile);
      $this->image_src = $this->uploadUrl . $this->imageFileName;
    }
    if ($this->promoMaterialsFile) {
      $this->promoMaterialsFileName = $this->generateFileName($this->promoMaterialsFile);
      $this->promo_materials_file_src = $this->uploadUrl . $this->promoMaterialsFileName;
    } else {
      $this->promo_materials_file_src = null;
    }

    return true;
  }

  private function generateFileName($uploadedFile)
  {
    if (!$uploadedFile instanceof UploadedFile) {
      return null;
    }
    $fileExploded = explode(".", $uploadedFile->name);
    return Yii::$app->security->generateRandomString(10) . '.' . end($fileExploded);
  }

  /**
   * @param bool $insert
   * @param array $changedAttributes
   * @throws Exception
   */
  public function afterSave($insert, $changedAttributes)
  {
    // Save image file
    $this->saveImage();

    // Save promo materials file
    $this->savePromoMaterial();

    // Save or delete landingOperators
    $this->saveOrDeleteOperators($insert);

    // Save or delete platforms
    $this->saveOrDeletePlatforms($insert);

    // Save or delete forbiddenTrafficTypes
    $this->saveOrDeleteForbiddenTrafficTypes($insert);

    if (array_key_exists('status', $changedAttributes) && $this->status == self::STATUS_ACTIVE) {
      SourceOperatorLanding::clearDisableHandled(['landing_id' => $this->id]);
    }

    $this->handleEvents($insert, $changedAttributes);

    $this->invalidateCache();

    (new LandingSetsLandsUpdater(['landingIds' => $this->category->getLanding()->select('id')->column()]))->run();

    parent::afterSave($insert, $changedAttributes);
  }

  protected function invalidateCache()
  {
    ApiHandlersHelper::clearCache('LandingsDataGroupByOperator');

    $landingOperators = $this->landingOperator;
    foreach($landingOperators as $landingOperator) {
      ApiHandlersHelper::clearCache('LandingsDataByOperator-' . $landingOperator->operator_id);
    }

    TagDependency::invalidate(Yii::$app->cache, ['landing']);
  }

  /**
   * @param $insert
   * @param $changedAttributes
   */
  protected function handleEvents($insert, $changedAttributes)
  {
    $isLandingVisible = $this->status === self::STATUS_ACTIVE && $this->access_type === self::ACCESS_TYPE_NORMAL;

    if ($insert && $this->scenario != self::SCENARIO_SYNC) {
      // Показываем уведомление партнерам, только если ленд видимый для них
      $isLandingVisible && (new LandingCreated($this))->trigger();
      (new LandingCreatedReseller($this))->trigger();

      return;
    }

    (new LandingUpdated($this))->trigger();

    // Если изменился статус или тип доступа, ленд стал видимым для партнера, показываем уведомление
    $oldStatus = ArrayHelper::getValue($changedAttributes, 'status');
    $oldAccessType = ArrayHelper::getValue($changedAttributes, 'status');

    if (
      ($oldStatus && $oldStatus != $this->status)
      || ($oldAccessType && $oldAccessType != $this->access_type)
    ) {
      $isLandingVisible && (new LandingCreated($this))->trigger();
    }

    // TODO: надо этот момент отрефакторить, чтобы заработало, т.к. LandingUnlocked ожидает инстанс модели заявки, а не ленда.
    /*$oldStatus = ArrayHelper::getValue($changedAttributes, 'status', false);
    if (
      $oldStatus == self::STATUS_BLOCKED &&
      in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_INACTIVE])
    ) {
      (new LandingUnlocked($this))->trigger();
    }*/

  }

  /**
   * @param $insert
   * @throws Exception
   */
  protected function saveOrDeleteOperators($insert)
  {
    if ($this->operatorModels === null) return;

    $oldIDs = $insert ? [] : ArrayHelper::map($this->landingOperator, 'operator_id', 'operator_id');

    foreach ($this->operatorModels as $operatorModel) {
      $operatorModel->landing_id = $this->id;
      if (!$operatorModel->save()) {
        Yii::error(sprintf(
          "Operator model save error:\n%s\nModel attributes:\n%s",
          json_encode($operatorModel->getErrors()),
          json_encode($operatorModel->getAttributes())
        ), __METHOD__);
      }


    }

    $deletedIDs = array_diff($oldIDs, array_filter(
        ArrayHelper::map($this->operatorModels, 'operator_id', 'operator_id'))
    );

    //помечаем удаленными
    if (!empty($deletedIDs)) {
      LandingOperator::updateAll(['is_deleted' => 1], ['operator_id' => $deletedIDs, 'landing_id' => $this->id]);
    }
  }

  /**
   * @param $insert
   * @throws Exception
   */
  protected function saveOrDeletePlatforms($insert)
  {
    if ($this->platformIds === null) return;

    $oldIDs = $insert ? [] : ArrayHelper::getColumn($this->landingPlatforms, 'id');

    $newIds = [];

    $this->platformIds = $this->platformIds ? : [];

    foreach ($this->platformIds as $platformId) {
      $model = LandingPlatform::findOrCreateModel($this->id, $platformId);

      if ($model->isNewRecord && !$model->save(false)) {
        throw new Exception('Landing platform model save error');
      }

      $newIds[] = $model->id;
    }

    $deletedIDs = array_diff($oldIDs, $newIds);

    if (!empty($deletedIDs)) LandingPlatform::deleteAll(['id' => $deletedIDs]);
  }


  /**
   * @param $insert
   * @throws Exception
   */
  protected function saveOrDeleteForbiddenTrafficTypes($insert)
  {
    if ($this->forbiddenTrafficTypeIds === null) return;

    $oldIDs = $insert ? [] : ArrayHelper::getColumn($this->landingForbiddenTrafficTypes, 'id');

    $newIds = [];

    $this->forbiddenTrafficTypeIds = $this->forbiddenTrafficTypeIds ? : [];

    foreach ($this->forbiddenTrafficTypeIds as $typeId) {
      $model = LandingForbiddenTrafficType::findOrCreateModel($this->id, $typeId);

      if ($model->isNewRecord && !$model->save(false)) {
        throw new Exception('Landing forbidden traffic type model save error');
      }

      $newIds[] = $model->id;
    }

    $deletedIDs = array_diff($oldIDs, $newIds);

    if (!empty($deletedIDs)) LandingForbiddenTrafficType::deleteAll(['id' => $deletedIDs]);
  }

  /**
   *
   */
  protected function saveImage()
  {
    $this->saveFile($this->imageFile, $this->imageFileName);
  }

  protected function savePromoMaterial()
  {
    $this->saveFile($this->promoMaterialsFile, $this->promoMaterialsFileName);
  }

  protected function saveFile($uploadedFile, $fileName)
  {
    /** @var UploadedFile $uploadedFile */
    if (!$uploadedFile instanceof UploadedFile) {
      return;
    }
    FileHelper::createDirectory($this->uploadPath);
    $uploadedFile->saveAs($this->uploadPath . $fileName);
  }

  /**
   * @param $data
   * @return bool
   */
  public function loadOperators($data)
  {
    $operatorModels = [];
    if (!empty($operators = ArrayHelper::getValue($data, 'LandingOperator', []))) {
      foreach ($operators as $operator) {
        $operatorModel = LandingOperator::findOrCreateModel(
          $this->id,
          ArrayHelper::getValue($operator, 'operator_id', null)
        );
        $operatorModel->setAttributes($operator);
        $operatorModels[] = $operatorModel;
      }
    }
    $this->operatorModels = $operatorModels;
    return true;
  }

  /**
   * @return string
   */
  public function getGridImg()
  {
    if (!$this->image_src) return '';
    return Html::a(
      Html::img($this->image_src, ['style' => 'max-width:100px']),
      $this->image_src,
      ['target' => 'blank', 'data-pjax' => 0]
    );
  }

  /**
   * @return array
   */
  public function getReplacements()
  {
    /** @var User $createdBy */
    $createdBy = $this->getCreatedBy()->one();
    return [
      'id' => [
        'value' => $this->isNewRecord ? null : $this->id,
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_id')
        ]
      ],
      'name' => [
        'value' => $this->isNewRecord ? null : $this->name,
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_name')
        ]
      ],
      'category' => [
        'value' => $this->isNewRecord ? null : $this->getReplacementCategory(),
        'help' => [
          'class' => LandingCategory::class,
          'label' => Yii::_t('promo.replacements.landing_category')
        ]
      ],
      'provider' => [
        'value' => $this->isNewRecord ? null : $this->getReplacementProvider(),
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_provider'),
          'class' => Provider::class
        ]
      ],
      'image_src' => [
        'value' => $this->isNewRecord ? null : $this->image_src,
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_image_src')
        ]
      ],
      'access_type' => [
        'value' => $this->isNewRecord ? null : $this->getCurrentAccessTypeName(),
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_access_type')
        ]
      ],
      'status' => [
        'value' => $this->isNewRecord ? null : $this->getCurrentStatusName(),
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_status')
        ]
      ],
      'description' => [
        'value' => $this->isNewRecord ? null : $this->description,
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_description')
        ]
      ],
      'rating' => [
        'value' => $this->isNewRecord ? null : $this->rating,
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_rating')
        ]
      ],
      'auto_rating' => [
        'value' => $this->isNewRecord ? null : $this->getReplacementAutoRating(),
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_auto_rating')
        ]
      ],
      'allow_sync_buyout_prices' => [
        'value' => $this->isNewRecord ? null : $this->allow_sync_buyout_prices,
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_allow_sync_buyout_prices')
        ]
      ],
      'send_id' => [
        'value' => $this->isNewRecord ? null : $this->send_id,
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_send_id')
        ]
      ],
      'createdBy' => [
        'value' => $this->isNewRecord ? null : $createdBy->getReplacements(),
        'help' => [
          'class' => Yii::$app->user->identityClass,
          'label' => Yii::_t('promo.replacements.landing_createdBy')
        ]
      ],
      'operators' => [
        'value' => $this->isNewRecord ? null : $this->getReplacementOperators(),
        'help' => [
          'label' => Yii::_t('promo.landings.operator-list'),
        ]
      ],
      'operatorNames' => [
        'value' => $this->isNewRecord ? null : $this->getOperatorsReplacement(),
        'help' => [
          'label' => Yii::_t('promo.landings.operator-names-replacement'),
        ]
      ],
      'countryCodes' => [
        'value' => $this->isNewRecord ? null : $this->getCountryCodesReplacement(),
        'help' => [
          'label' => Yii::_t('promo.landings.country-codes-replacement'),
        ]
      ]
    ];
  }

  /**
   * @return mixed
   */
  public function getReplacementCategory()
  {
    $relatedModel = $this->getCategory()->one();
    if ($relatedModel) return $relatedModel->getReplacements();
  }

  /**
   * @return mixed
   */
  public function getReplacementProvider()
  {
    $relatedModel = $this->getProvider()->one();
    if ($relatedModel) return $relatedModel->getReplacements();
  }

  /**
   * @return string
   */
  public function getReplacementAutoRating()
  {
    return $this->auto_rating ? Yii::_t('app.common.Yes') : Yii::_t('app.common.No');
  }

  /**
   * @return string
   */
  public function getReplacementOperators()
  {
    $replacement = [];

    foreach ($this->landingOperator as $operator) {
      $replacement[] = ArrayHelper::toArray($operator)/*->getReplacements()*/;
    }

    return json_encode($replacement);
  }


  public function getReplacementUnblockRequest()
  {
    $unblockModel = $this->unblockRequest;
    if ($unblockModel) return $unblockModel->getReplacements();
  }

  /**
   * @return string
   */
  public function getViewLink()
  {
    return \mcms\common\helpers\Link::get(
      '/promo/landings/view',
      ['id' => $this->id], ['data-pjax' => 0], $this->getStringInfo(),
      false
    );
  }

  /**
   * По объекту Landing возвращает отформатированную строку вида:
   * "#35 - Landing name"
   *
   * @return string
   */
  public function getStringInfo()
  {
    return sprintf(
      '#%s - %s',
      ArrayHelper::getValue($this, 'id'),
      ArrayHelper::getValue($this, 'name')
    );
  }


  public function getRequest()
  {
    return $this->landingUnblockRequestCurrentUser;
    return LandingUnblockRequest::findOne([
      'landing_id' => $this->id,
      'user_id' => Yii::$app->user->id,
      ]);
  }

  /**
   * Тип доступа по запросу
   * @return bool
   */
  public function isByRequest()
  {
    return $this->access_type == Landing::ACCESS_TYPE_BY_REQUEST;
  }

  /**
   * Тип доступа скрытый
   * @return bool
   */
  public function isHidden()
  {
    return $this->access_type == Landing::ACCESS_TYPE_HIDDEN;
  }

  /**
   * Тип доступа нормальный
   * @return bool
   */
  public function isNormal()
  {
    return $this->access_type == Landing::ACCESS_TYPE_NORMAL;
  }

  public function isRequestStatusNotUnlocked()
  {
    $request = $this->landingUnblockRequestCurrentUser;
    return $this->isByRequest() && (!$request || ($request->status !== LandingUnblockRequest::STATUS_UNLOCKED));
  }

  public function isRequestStatusUnlocked()
  {
    $request = $this->landingUnblockRequestCurrentUser;
    return $this->isByRequest() && (!$request || ($request->status === LandingUnblockRequest::STATUS_UNLOCKED));
  }

  public function isRequestStatusBlocked()
  {
    return $this->isByRequest()
      && ($request = $this->landingUnblockRequestCurrentUser)
      && $request->status == LandingUnblockRequest::STATUS_DISABLED
      ;
  }

  public function isRequestStatusModeration()
  {
    return  $this->isByRequest() && $this->getRequest() && $this->getRequest()->status == LandingUnblockRequest::STATUS_MODERATION;
  }

  public function isHiddenBlocked()
  {
    if (!$this->isHidden()) return false;
    if (!$this->getRequest()) return true;
    if ($this->getRequest()->status !== LandingUnblockRequest::STATUS_UNLOCKED) return true;

    return false;
  }

  public function getLandingUnblockRequestCurrentUser()
  {
    return $this
      ->hasOne(LandingUnblockRequest::class, ['landing_id' => 'id'])
      ->andWhere([
        'or',
        'landing_unblock_requests.user_id is null',
        ['landing_unblock_requests.user_id' => Yii::$app->user->getId()]
      ]);
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
   * Лендинг скрыт
   * @return bool
   */
  public function isHiddenByRequest()
  {
    return in_array($this->access_type, [
      self::ACCESS_TYPE_BY_REQUEST,
      self::ACCESS_TYPE_HIDDEN
    ], true);
  }


  /**
   * Пример:
   * #19 - OPERATOR19 (COUNTRY13) (me) - 62,00 руб. / 1,00 руб.
   * #29 - OPERATOR29 (COUNTRY8) (sq) - 25,00 руб. / 55,00 руб.
   *
   * @return string
   */
  public function getGridOperators()
  {
    $operators = array_map(function($landingOperator){
      /** @var LandingOperator $landingOperator */
      $rebillBuyOut = $landingOperator->getConvertedBuyOutRebillString();
      return $landingOperator->operator->getViewLink($rebillBuyOut, $landingOperator->is_deleted);
    }, $this->landingOperator);

    return implode('<br/>', $operators);
  }

  public function getGridCountries()
  {
    $countries = [];
    foreach($this->landingOperator as $landingOperator) {
      $country = $landingOperator->operator->country;
      if (!isset($countries[$country->id])) {
        $countries[$country->id] = $country->getViewLink($landingOperator->is_deleted);
      }
    }

    return implode('<br/>', $countries);
  }

  public function getOperatorsReplacement()
  {
    $operators = array_map(function(LandingOperator $landingOperator){
      return $landingOperator->operator->name;
    }, $this->landingOperator);

    return implode(', ', $operators);
  }

  public function getCountryCodesReplacement()
  {
    $operators = array_map(function(LandingOperator $landingOperator){
      return $landingOperator->operator->country->code;
    }, $this->landingOperator);

    return implode(', ', $operators);
  }

  public static function getViewUrl($id, $asString = false)
   {
     $arr = ['/promo/landings/view', 'id' => $id];
     return $asString ? Url::to($arr) : $arr;
   }

  /**
   * Метод возвращает массив лендингов разбитый по категориям.
   * @param bool $isActive
   * @return array
   */
  public static function getLandingsByCategory($isActive = true, $landingIds = [], $filterName = '', $cache = true)
  {
    $key = $isActive
      ? self::LANDINGS_BY_CATEGORY_ACTIVE_CACHE_KEY
      : self::LANDINGS_BY_CATEGORY_ALL_CACHE_KEY
    ;

    if (!empty($landingIds)) {
      $key .= 'with-landings-' . serialize($landingIds);
    }

    $items = false;
    if ($cache) {
      $items = Yii::$app->cache->get($key);
    }
    if ($items === false) {
      $landings = self::find()
        ->joinWith('category')
        ->orderBy([
          LandingCategory::tableName() . '.name' => SORT_ASC,
          Landing::tableName() . '.name' => SORT_ASC
        ]);

      if ($isActive) {
        $landings->where([self::tableName() . '.status' => Landing::STATUS_ACTIVE]);
      }

      if (!empty($landingIds)) {
        $landings->andWhere([self::tableName() . '.id' => $landingIds]);
      }

      if ($filterName) {
        $landings->andWhere(['or', ['like', self::tableName() . '.name', $filterName], [self::tableName() . '.id' => $filterName]]);
      }

      /** @var Landing[] $landings */
      $items = [];
      foreach ($landings->each() as $landing) {
        $items[(string) $landing->category->name][$landing->id] = '#' . $landing->id . ' - ' . $landing->name;
      }

      if ($cache) {
        Yii::$app->cache->set(
          $key,
          $items,
          3600,
          new TagDependency(['tags' => self::LANDINGS_BY_CATEGORY_CACHE_TAGS])
        );
      }
    }
    return $items;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public static function getInactiveLandingQuery()
  {
    return static::find()
      ->where(['<>', 'status', self::STATUS_ACTIVE])
      ;
  }

  public function getPromoMaterialsFileName()
  {
    return basename($this->promo_materials_file_src);
  }

  public function getActualPromoMaterials()
  {
    return $this->promo_materials_file_src ? : $this->promo_materials;
  }

  /**
   * Возвращает домен сервиса
   * @return string
   */
  public function getServiceDomain()
  {
    $parseUrl = parse_url($this->service_url);
    return ArrayHelper::getValue($parseUrl, 'host');
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getActiveLandingOperators()
  {
    return $this
      ->hasMany(LandingOperator::class, ['landing_id' => 'id'])
      ->from(['alo' => LandingOperator::tableName()])
      ->andWhere(['alo.is_deleted' => 0])
      ;
  }

  /**
   * Одинаковый ли ленд для всех операторов или оператор один
   * @return bool
   */
  public function isSameForOperators()
  {
    /** @var \mcms\partners\Module $partnersModule */
    $partnersModule = Yii::$app->getModule('partners');
    if (!$partnersModule->isMergeLandings()) {
      return false;
    }

//    /* @var $landingOperator LandingOperator */
//    $landingOperators = LandingOperator::find()->where([
//        'landing_id' => $this->id,
//        'is_deleted' => 0,
//      ]);

    if (count($this->activeLandingOperators) <= 1) {
      return false;
    }

    $landings = [];
    foreach ($this->activeLandingOperators as $landingOperator) {
      /* @var $landingOperator LandingOperator */
      $prices = LandingOperatorPrices::create($landingOperator, Yii::$app->user->id);

      $landings[] = [
        'days_hold' => $landingOperator->days_hold,
        'default_currency_id' => $landingOperator->default_currency_rebill_price,
        'buyout_price' => $prices->getBuyoutProfit(),
        'rebill_price_usd' => $prices->getRebillPrice('usd'),
        'rebill_price_eur' => $prices->getRebillPrice('eur'),
        'rebill_price_rub' => $prices->getRebillPrice('rub'),
        'subscription_type_id' => $landingOperator->subscription_type_id,
        'is_deleted' => $landingOperator->is_deleted,
      ];
    }

    $compare = true;
    $compareLandings = array_shift($landings);
    foreach ($landings as $landing) {
      if ($landing !== $compareLandings) {
        $compare = false;
        break;
      }
    }

    return $compare;
  }

  /**
   *  Операторы лендинга доступные партнеру через запятую
   * @param int $userId
   * @return string
   */
  public function getLandingOperators($userId)
  {
    $indexedAvailableOperators = array_flip(AvailableOperators::getInstance($userId)->getIds());

    $operators = [];
    foreach ($this->activeLandingOperators as $landingOperator) {
      if (!array_key_exists($landingOperator->operator_id, $indexedAvailableOperators)) continue;

      $operators[] = $landingOperator->operator_id;
    }

    return implode(',', $operators);

//    return implode(',', LandingOperator::find()->select('operator_id')->where([
//      'landing_id' => $this->id,
//      'operator_id' => AvailableOperators::getInstance($userId)->getIds(),
//    ])->column());
  }

  public function copyLanding()
  {
    $copyAttributes = [
      'name',
      'service_url',
      'category_id',
      'offer_category_id',
      'provider_id',
      'image_src',
      'access_type',
      'status',
      'description',
      'custom_url',
      'comment',
      'rating',
      'auto_rating',
      'created_by',
      'created_at',
      'updated_at',
      'sync_updated_at',
      'allow_sync_buyout_prices',
      'allow_sync_status',
      'allow_sync_access_type',
      'to_landing_id',
      'send_id',
      'operators_text',
      'rebill_period',
      'promo_materials',
      'promo_materials_file_src',
    ];

    $transaction = Yii::$app->getDb()->beginTransaction();

    $model = new Landing();
    $model->setScenario(Landing::SCENARIO_COPY);

    foreach ($copyAttributes as $attribute) {
      $model->setAttribute($attribute, $this->getAttribute($attribute));
    }

    $model->name .= ' copy';
    $model->status = Landing::STATUS_INACTIVE;

    if (!$model->save()) {
      $transaction->rollBack();
      return null;
    }
    try {


      // Скопировать
      // landing_forbidden_traffic_types
      $landingForbiddenTrafficTypes = (new Query())
        ->from('landing_forbidden_traffic_types')
        ->where(['landing_id' => $this->id])
        ->all();

      $landingForbiddenTrafficTypes = array_map(function ($item) use ($model) {
        unset($item['id']);
        $item['landing_id'] = $model->id;
        return $item;
      }, $landingForbiddenTrafficTypes);

      Yii::$app->getDb()
        ->createCommand()
        ->batchInsert(
          'landing_forbidden_traffic_types',
          ['landing_id', 'forbidden_traffic_type_id'],
          $landingForbiddenTrafficTypes
        )
        ->execute();

      // landing_operator_pay_types
      $landingOperatorPayType = (new Query())
        ->from('landing_operator_pay_types')
        ->where(['landing_id' => $this->id])
        ->all();

      $landingOperatorPayType = array_map(function ($item) use ($model) {
        unset($item['id']);
        $item['landing_id'] = $model->id;
        return $item;
      }, $landingOperatorPayType);

      Yii::$app->db
        ->createCommand()
        ->batchInsert(
          'landing_operator_pay_types',
          ['landing_id', 'operator_id', 'landing_pay_type_id'],
          $landingOperatorPayType
        )
        ->execute();

      // landing_operators
      $landingOperators = (new Query)
        ->from('landing_operators')
        ->where(['landing_id' => $this->id])
        ->all();

      $landingOperators = array_map(function ($item) use ($model) {
        $item['landing_id'] = $model->id;
        return $item;
      }, $landingOperators);

      Yii::$app->db
        ->createCommand()
        ->batchInsert(
          'landing_operators',
          [
            'landing_id',
            'operator_id',
            'days_hold',
            'default_currency_id',
            'default_currency_rebill_price',
            'local_currency_id',
            'local_currency_rebill_price',
            'buyout_price_usd',
            'buyout_price_eur',
            'buyout_price_rub',
            'rebill_price_usd',
            'rebill_price_eur',
            'rebill_price_rub',
            'cost_price',
            'subscription_type_id',
            'created_at',
            'updated_at',
            'is_deleted',
            'use_landing_operator_rebill_price'
          ],
          $landingOperators
        )
        ->execute();

      // landing_platforms
      $landingPlatforms = (new Query())
        ->from('landing_platforms')
        ->where(['landing_id' => $this->id])
        ->all();

      $landingPlatforms = array_map(function ($item) use ($model) {
        unset($item['id']);
        $item['landing_id'] = $model->id;
        return $item;
      }, $landingPlatforms);

      Yii::$app->db
        ->createCommand()
        ->batchInsert('landing_platforms', ['landing_id', 'platform_id'], $landingPlatforms)
        ->execute();

      // sources_operator_landings (ссылки, арбитраж)
/*
      $sourceOperatorLandings = (new Query())
        ->from('sources_operator_landings')
        ->where(['landing_id' => $this->id])
        ->all();
      $sourceOperatorLandings = array_map(function ($item) use ($model) {
        unset($item['id']);
        $item['landing_id'] = $model->id;
        return $item;
      }, $sourceOperatorLandings);
      Yii::$app->db
        ->createCommand()
        ->batchInsert(
          'sources_operator_landings', [
          'source_id',
          'profit_type',
          'operator_id',
          'landing_id',
          'is_changed',
          'change_description',
          'landing_choose_type',
          'is_disable_handled',
          'rating',
        ],
          $sourceOperatorLandings
        )
        ->execute();
*/
      // sources_operator_landings_excluded
      $sourcesOperatorLandingsExcluded = (new Query)
        ->from('sources_operator_landings_excluded')
        ->where(['landing_id' => $this->id])
        ->all();

      $sourcesOperatorLandingsExcluded = array_map(function ($item) use ($model) {
        $item['landing_id'] = $model->id;
        return $item;
      }, $sourcesOperatorLandingsExcluded);

      Yii::$app->db
        ->createCommand()
        ->batchInsert(
          'sources_operator_landings_excluded',
          ['source_id', 'landing_id', 'operator_id', 'created_at', 'rating'],
          $sourcesOperatorLandingsExcluded
        )
        ->execute();

      // personal_profit (доходность)
/*
      $personalProfit = (new Query)
        ->from('personal_profit')
        ->where(['landing_id' => $this->id])
        ->all();

      $personalProfit = array_map(function ($item) use ($model) {
        $item['landing_id'] = $model->id;
        return $item;
      }, $personalProfit);

      Yii::$app->db
        ->createCommand()
        ->batchInsert('personal_profit', [
          'user_id',
          'operator_id',
          'landing_id',
          'provider_id',
          'country_id',
          'rebill_percent',
          'buyout_percent',
          '_cpa_profit',
          'cpa_profit_rub',
          'cpa_profit_usd',
          'cpa_profit_eur',
          'created_by',
          'created_at',
          'updated_at',
        ], $personalProfit)
        ->execute();
*/

      $transaction->commit();
      return $model;
    } catch (\Exception $e) {
  
      $message = $e->getName()." '" . get_class($e) . "' with message '{$e->getMessage()}' \n\nin "
        . $e->getFile() . ':' . $e->getLine() . "\n\n"
        . "Stack trace:\n" . $e->getTraceAsString();
      Yii::error($message);
      
      $transaction->rollBack();
      return null;
    }
  }


}
