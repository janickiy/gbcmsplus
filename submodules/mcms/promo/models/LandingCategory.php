<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\common\helpers\Link;
use mcms\common\multilang\MultiLangModel;
use mcms\common\traits\Translate;
use mcms\promo\components\api\Banners as BannerApi;
use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\components\landing_sets\LandingSetsLandsUpdater;
use mcms\promo\models\search\LandingSearch;
use mcms\promo\models\traits\LinkBanners;
use Yii;
use mcms\promo\components\events\LandingCategoryCreated;
use mcms\promo\components\events\LandingCategoryUpdated;
use mcms\user\models\User;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\caching\TagDependency;
use yii\db\Query;
use yii\helpers\BaseInflector;

/**
 * This is the model class for table "{{%landing_categories}}".
 *
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property integer $status
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property array $alter_categories
 * @property string $click_n_confirm_text
 * @property integer $is_not_mainstream
 *
 * @property Banner[] $banners
 * @property User $createdBy
 * @property Landing[] $landings
 */
class LandingCategory extends MultiLangModel
{
  use Translate, LinkBanners;

  const LANG_PREFIX = 'promo.landing_categories.';

  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;

  const SCENARIO_CREATE = 'create';
  const SCENARIO_UPDATE = 'update';

  const DELIMITER = ',';
  const PERMISSION_PREFIX = 'CanEditLandingCategory';
  const ADS_CLICK_N_CONFIRM_TEXT_CACHE_KEY_PREFIX = 'AdsConfirmTest_sourceId';
  const LINK_BANNERS_TABLE = 'landing_categories_banners';
  const LINK_BANNERS_FIELD = 'category_id';

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
      [
        'class' => BlameableBehavior::class,
        'updatedByAttribute' => null,
      ]
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%landing_categories}}';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['bannersIds', 'each', 'rule' => ['integer']],
      ['created_by', 'default', 'value' => isset(Yii::$app->user) ? Yii::$app->user->id : null],
      [['code', 'name', 'created_by', 'status', 'alter_categories'], 'required'],
      [['status', 'created_by', 'is_not_mainstream'], 'integer'],
      [['name', 'click_n_confirm_text'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['name'], 'validateArrayString', 'params' => ['min' => 1, 'max' => 50]],
      [['code'], 'string'],
      [['code'], 'unique'],
      [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
      [['alter_categories'], 'alterCategoryValidator'],
      [['alter_categories'], 'each', 'rule' => ['string', 'min' => 1]],
      [['alter_categories'], 'each', 'rule' => ['exist', 'targetClass' => LandingCategory::class, 'targetAttribute' => 'code']],
      ['tb_url', 'url', 'when' => function(){
        return static::canEditAttribute('tb_url');
      }]
    ];
  }

  /**
   * @param $attribute
   * @return bool
   */
  public static function canEditAttribute($attribute)
  {
    return Yii::$app->user->can(self::PERMISSION_PREFIX . BaseInflector::camelize($attribute));
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    $additionalAttribute = [];
    $attributes = ['name', 'status', 'bannersIds', 'alter_categories', 'tb_url', 'click_n_confirm_text', 'is_not_mainstream'];
    foreach ($attributes as $attr) {
      if ($this->canEditAttribute($attr)) $additionalAttribute[] = $attr;
    }

    $scenarios = parent::scenarios() + [
        self::SCENARIO_CREATE => array_merge(['code'], $additionalAttribute),
        self::SCENARIO_UPDATE => $additionalAttribute,
      ];

    return $scenarios;
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'code',
      'name',
      'status',
      'alter_categories',
      'created_by',
      'created_at',
      'updated_at',
      'tb_url',
      'click_n_confirm_text',
      'is_not_mainstream',
    ]);
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
   * @deprecated
   */
  public function getLanding()
  {
    return $this->getLandings();
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLandings()
  {
    return $this->hasMany(Landing::class, ['category_id' => 'id']);
  }


  public function getActiveLandings()
  {
    return $this->hasMany(Landing::class, ['category_id' => 'id'])
      ->andWhere(['status' => Landing::STATUS_ACTIVE]);
  }

  public function getActiveLandingsCount()
  {
    return $this->getActiveLandings()->count();
  }


  public function getStatuses($status = null)
  {
    $list = [
      self::STATUS_INACTIVE => self::translate('status-inactive'),
      self::STATUS_ACTIVE => self::translate('status-active')
    ];
    return isset($status) ? ArrayHelper::getValue($list, $status, null) : $list;
  }

  public function getTbUrl()
  {
    return $this->getAttribute('tb_url');
  }

  public function getCurrentStatusName()
  {
    return $this->getStatuses($this->status);
  }

  public function getReplacements()
  {
    /** @var User $createdBy */
    $createdBy = $this->getCreatedBy()->one();
    return [
      'id' => [
        'value' => $this->isNewRecord ? null : $this->id,
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_category_id')
        ]
      ],
      'code' => [
        'value' => $this->isNewRecord ? null : $this->code,
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_category_code')
        ]
      ],
      'name' => [
        'value' => $this->isNewRecord ? null : $this->name,
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_category_name')
        ]
      ],
      'status' => [
        'value' => $this->isNewRecord ? null : $this->getCurrentStatusName(),
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_category_status')
        ]
      ],
      'createdBy' => [
        'value' => $this->isNewRecord ? null : $createdBy->getReplacements(),
        'help' => [
          'class' => Yii::$app->user->identityClass,
          'label' => Yii::_t('promo.replacements.landing_category_createdBy')
        ]
      ]
    ];
  }

  public function beforeValidate()
  {
    if (!parent::beforeValidate()) return false;

    if ($this->isFormUpdate()) {
      if (!is_array($this->alter_categories)) $this->alter_categories = explode(self::DELIMITER, $this->alter_categories);
    }

    return true;
  }

  public function beforeSave($insert)
  {
    if (!parent::beforeSave($insert)) return false;

    if ($this->isFormUpdate()) {
      $this->alter_categories = serialize($this->alter_categories);
    }

    return true;
  }

  public function afterFind()
  {
    parent::afterFind();

    $this->alter_categories = (empty($this->alter_categories) || !($alterCategories = @unserialize($this->alter_categories)))
      ? []
      : $alterCategories;
  }

  public function afterSave($insert, $changedAttributes)
  {
    $insert
      ? (new LandingCategoryCreated($this))->trigger()
      : (new LandingCategoryUpdated($this))->trigger()
    ;

    $this->invalidateCache();

    (new LandingSetsLandsUpdater(
      ['landingIds' => [ArrayHelper::getColumn($this->getLanding()->all(), 'id')]]
    ))->run();

    parent::afterSave($insert, $changedAttributes);
  }

  public function invalidateCache()
  {
    TagDependency::invalidate(Yii::$app->cache, ['landing']);

    $apiKeys = ['promo.tb_urls', 'settings.default_tb_url', 'promo.landing_categories_tb_urls'];
    $apiKeys = array_merge($apiKeys, $this->getAdsClickNConfirmTextCacheKeys());
    ApiHandlersHelper::clearCache($apiKeys);

    BannerApi::clearSelectedBannerCache();

  }

  public function afterDelete()
  {
    $this->invalidateCache();
    parent::afterDelete();
  }

  /**
   * @return string
   */
  public function getViewLink()
  {
    return Link::get('/promo/landing-categories/view', ['id' => $this->id], ['data-pjax' => 0], $this->getStringInfo(), false);
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
   * @param bool $activeOnly
   * @param string $from
   * @param string $to
   * @return array
   */
  public static function getAllMap($activeOnly = true, $from = 'id', $to = 'name')
  {
    $categories = self::find()->orderBy('name');
    if ($activeOnly) $categories->where('status = :active_status', [':active_status' => LandingCategory::STATUS_ACTIVE]);
    return ArrayHelper::map($categories->each(), $from, $to);
  }

  public function getLandingsLink()
  {
    return Link::get(
      'landings/index',
      [(new LandingSearch())->formName() . '[category_id]' => $this->id],
      ['data-pjax' => 0],
      Yii::_t('promo.landings.main')) . ' ' . Html::tag('span', $this->activeLandingsCount, ['class' => 'label label-default']);
  }

  /**
   * @return array
   */
  public function getMultilangAttributes()
  {
    return ['name', 'click_n_confirm_text'];
  }

  public function getAlterCategories()
  {
    if (empty($this->alter_categories)) return [];
    $alterCategories = is_array($this->alter_categories) ? $this->alter_categories : explode(self::DELIMITER, $this->alter_categories);

    $alters = self::find()->where(['code' => $alterCategories, 'status' => self::STATUS_ACTIVE])->indexBy('code')->all();

    return array_filter(array_map(function ($alterCode) use ($alters) {
        return ArrayHelper::getValue($alters, $alterCode);
      }, $alterCategories)
    );
  }

  /**
   * Получение категорий для селекта
   * @param string $ignoreCode
   * @return array
   */
  public static function getDropdownItems($ignoreCode = '')
  {
    $query = static::find()->andFilterWhere(['!=', 'code', $ignoreCode])->orderBy('id DESC');

    return ArrayHelper::map($query->each(), 'code', function($category) {
      return (string) $category->name;
    });
  }

  /**
   * Происходит ли обновление данных в админке
   * @return bool
   */
  public function isFormUpdate()
  {
    return in_array($this->scenario, [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]);
  }

  /**
   * Проверка, что код альтернативной категории не равен текущему коду
   * @param string $attribute
   * @param array $params
   */
  public function alterCategoryValidator($attribute, $params) {
    foreach ($this->alter_categories as $category) {
      if ($category == $this->code) {
        $this->addError('alter_categories', self::translate('error-same-category'));
        break;
      }
    }
  }

  /**
   * @return array
   */
  private function getAdsClickNConfirmTextCacheKeys()
  {
    $sourceIds = (new Query())
      ->select('id')
      ->from(Source::tableName())
      ->where(['category_id' => $this->id])
      ->column();

    if (empty($sourceIds)) return [];

    return array_map(function($sourceId){
      return self::ADS_CLICK_N_CONFIRM_TEXT_CACHE_KEY_PREFIX . $sourceId;
    }, $sourceIds);
  }
}
