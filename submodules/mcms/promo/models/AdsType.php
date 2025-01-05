<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use mcms\promo\Module;
use Yii;
use yii\behaviors\TimestampBehavior;
use mcms\common\multilang\MultiLangModel;
use yii\caching\TagDependency;

/**
 * This is the model class for table "ads_types".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property string $description
 * @property integer $is_default
 * @property integer $status
 * @property integer $security
 * @property integer $profit
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property array $statuses
 */
class AdsType extends MultiLangModel
{

  use Translate;
  const LANG_PREFIX = 'promo.ads-types.';

  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;
  const STATUS_BLOCKED = 2;

  const CACHE_PREFIX = 'promo_ads_types';
  const CACHE_TAG_GLOBAL = self::CACHE_PREFIX . '_global';

  const CLICK_N_CONFIRM_CODE = 'click_n_confirm';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'ads_types';
  }

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
  public function rules()
  {
    return [
      ['created_by', 'default', 'value' => isset(Yii::$app->user) ? Yii::$app->user->id : 0],
      ['updated_by', 'filter', 'filter' => function () { return isset(Yii::$app->user) ? Yii::$app->user->id : 0; }],
      [['code', 'status', 'name', 'description', 'created_by', 'updated_by'], 'required'],
      [['is_default', 'status', 'security', 'profit', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
      [['code'], 'string', 'max' => 30],
      [['name', 'description'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['name', 'description'], 'validateArrayRequired'],
      [['name', 'description'], 'validateArrayString'],
      ['code', 'unique', 'targetAttribute' => ['code', 'status']],
    ];
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
      'description',
      'is_default',
      'status',
      'security',
      'profit',
      'created_at',
      'updated_at',
      'created_by',
      'updated_by'
    ]);
  }

  /**
   * @inheritdoc
   */
  public function getMultilangAttributes()
  {
    return ['name', 'description'];
  }

  /**
   * @return array
   */
  public static function getAvailableSecurity()
  {
    $arr = [1, 2, 3, 4, 5];
    return array_combine($arr, $arr);
  }

  /**
   * @return array
   */
  public static function getAvailableProfit()
  {
    $arr = [1, 2, 3, 4, 5];
    return array_combine($arr, $arr);
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
   * @param null $status
   * @return array
   */
  public function getStatuses($status = null)
  {

    $list = [
      self::STATUS_INACTIVE => self::translate('status-inactive'),
      self::STATUS_ACTIVE => self::translate('status-active')
    ];
    if (self::canViewBlocked()) {
      $list[self::STATUS_BLOCKED] = self::translate('status-blocked');
    }
    return isset($status) ? ArrayHelper::getValue($list, $status, null) : $list;
  }

  /**
   * @inheritdoc
   */
  public static function findAll($condition)
  {
    $key = self::CACHE_PREFIX . md5(serialize($condition));

    $cachedData = Yii::$app->cache->get($key);

    if ($cachedData) return $cachedData;

    $data = parent::findAll($condition);

    $cacheDependency = new TagDependency(['tags' => [
      self::CACHE_TAG_GLOBAL
    ]]);

    Yii::$app->cache->set($key, $data, 3600, $cacheDependency);

    return $data;
  }


  /**
   * @param $code
   * @return AdsType|null
   */
  public function findByCode($code)
  {
    $models = self::findAll(['code' => $code]);
    if ($models && count($models) > 0) {
      reset($models); // без резета current() вернет false
      return current($models);
    }

    return null;
  }

  /**
   * @param $id
   * @return AdsType|null
   */
  public static function findById($id)
  {
    $models = self::findAll(['id' => $id]);
    if ($models && count($models) > 0) {
      reset($models); // без резета current() вернет false
      return current($models);
    }

    return null;
  }

  /**
   * @return array
   */
  public static function getDropDown()
  {
    $models = self::findAll(['status' => self::STATUS_ACTIVE]);

    return ArrayHelper::map($models, 'id', 'name');
  }

  public static function clearAllCache()
  {
    TagDependency::invalidate(Yii::$app->cache, [
      self::CACHE_TAG_GLOBAL,
    ]);
  }

  /**
   * @inheritdoc
   */
  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);
    self::clearAllCache();
  }

  public function canView()
  {
    if (!$this->isBlocked()) return true;
    return self::canViewBlocked();
  }

  protected static function canViewBlocked()
  {
    return Yii::$app->user->can('PromoCanViewAdsBlockedTypes');
  }
  
  public function isBlocked()
  {
    return $this->status === self::STATUS_BLOCKED;
  }

  /**
   * Если заблокирован формат, то вернет false
   * @return bool
   */
  public static function isClickNConfirmAvailable()
  {
    if (!$model = AdsType::findOne(['code' => self::CLICK_N_CONFIRM_CODE])) return false;

    if ($model->isBlocked()) return false;

    return true;
  }

  public function getAdditionalDescription(Source $source = null)
  {
    switch ($this->code) {
      case 'replace_links':
        $cssClass = $source && $source->replace_links_css_class
          ? $source->replace_links_css_class
          : Yii::$app->getModule('promo')->settings->getValueByKey(Module::SETTINGS_LINKS_REPLACEMENT_CLASS);

        $result = $this->description . '. ' . Yii::_t('promo.ads-types.ads_type_additional_description_' . $this->code, ['cssClass' => $cssClass, 'hasCssClass' => (int)(bool)$cssClass]);
        break;
      default:
        $result = $this->description;
    }
    return $result;
  }
}