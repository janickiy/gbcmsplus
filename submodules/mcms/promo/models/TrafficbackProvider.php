<?php

namespace mcms\promo\models;

use mcms\common\traits\model\Disabled;
use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\components\events\TrafficbackProviderCreated;
use mcms\promo\components\events\TrafficbackProviderUpdated;
use mcms\promo\components\TBProviderStatusChecker;
use mcms\user\models\User;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%trafficback_providers}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $url
 * @property integer $status
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $category_id
 * @property LandingCategory $category
 *
 * @property User $createdBy
 */
class TrafficbackProvider extends \yii\db\ActiveRecord implements \JsonSerializable
{
  use Translate;
  use Disabled;


  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;

  const TRAFFICBACK_PROVIDER_CACHE_KEY = 'trafficback_provider_cache_key';
  const LANG_PREFIX = 'promo.trafficback_providers.';

  public function behaviors()
  {
    return [
      TimestampBehavior::class,
      [
        'class' => BlameableBehavior::class,
        'createdByAttribute' => 'created_by',
        'updatedByAttribute' => false,
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%trafficback_providers}}';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['name', 'url', 'status'], 'required'],
      [['name', 'url'], 'unique'],
      [['status', 'category_id', 'created_by'], 'integer'],
      // приведение полей к int. Иначе они считаются строками и всегда присутствуют в $changedAttributes
      [['status', 'category_id'], 'filter', 'filter' => 'intval'],
      [['name'], 'string', 'max' => 100],
      [['url'], 'string', 'max' => 255],
      ['url', 'url'],
      [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => LandingCategory::class, 'targetAttribute' => ['category_id' => 'id'], 'when' => function ($model) {
        return (bool)$model->category_id;
      }],
      [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
    ];
  }


  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'name',
      'url',
      'status',
      'created_by',
      'created_at',
      'updated_at',
      'category_id',
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
   */
  public function getCategory()
  {
    return $this->hasOne(LandingCategory::class, ['id' => 'category_id']);
  }

  public function getStatuses($status = null)
  {
    $list = [
      self::STATUS_INACTIVE => Yii::_t('promo.providers.status-inactive'),
      self::STATUS_ACTIVE => Yii::_t('promo.providers.status-active')
    ];
    return isset($status) ? ArrayHelper::getValue($list, $status, null) : $list;
  }

  /**
   * @param bool $activeOnly
   * @return array
   */
  public function getCategoriesMap($activeOnly = true)
  {
    return LandingCategory::getAllMap($activeOnly);
  }

  /**
   * Имя текущего статуса
   * @return string
   */
  public function getCurrentStatusName()
  {
    return $this->getStatuses($this->status);
  }

  /**
   * Имя текущей категории
   * @return string
   */
  public function getCurrentCategoryName()
  {
    return $this->category
      ? $this->category->name
      : '';
  }

  public function afterSave($insert, $changedAttributes)
  {
    if ($insert) {
      (new TrafficbackProviderCreated($this))->trigger();
    } else {
      (new TrafficbackProviderUpdated($this))->trigger();
    }
    // tricky: Если сменили категорию или активировали провайдер, ищем активные провайдеры с такой же категорией и делаем неактивными
    (new TBProviderStatusChecker($this))->disableSameCategoryProviders($insert, $changedAttributes);
    $this->invalidateCache();

    parent::afterSave($insert, $changedAttributes);
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

  function jsonSerialize()
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'url' => $this->url,
      'status' => $this->status,
      'categoryId' => $this->category_id,
      'createdBy' => $this->created_by,
      'createdAt' => $this->created_at,
      'updatedAt' => $this->updated_at,
    ];
  }

  protected function invalidateCache()
  {
    ApiHandlersHelper::clearCache(self::TRAFFICBACK_PROVIDER_CACHE_KEY);
  }

}
