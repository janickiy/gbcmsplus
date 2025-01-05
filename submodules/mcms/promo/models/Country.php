<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Link;
use mcms\common\validators\AlphanumericalValidator;
use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\components\landing_sets\LandingSetsLandsUpdater;
use mcms\promo\models\search\OperatorSearch;
use rgk\utils\traits\ModelToStringTrait;
use Yii;
use mcms\promo\components\events\CountryCreated;
use mcms\promo\components\events\CountryUpdated;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%countries}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $status
 * @property string $code
 * @property string $currency
 * @property string $local_currency
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $sync_updated_at
 * @property string $currentStatusName
 *
 * @property Operator[] $operator
 * @property Operator[] $activeOperator
 */
class Country extends \yii\db\ActiveRecord
{
  use ModelToStringTrait;

  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;

  private $_landingsCount;
  private $_activeLandingsCount;

  /** @var  self[] */
  private static $_countries;

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
    return '{{%countries}}';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['name', 'code', 'status'], 'required'],
      [['status'], 'integer'],
      [['name'], 'string', 'max' => 50],
      [['code'], 'string', 'max' => 10],

      [['currency','local_currency'], 'string', 'max' => 3],
      [['currency','local_currency'], 'filter', 'filter' => 'strtolower'],
      ['currency', 'in', 'range' => ['rub', 'usd', 'eur']],

      [['code'], 'unique'],
      [['code'], AlphanumericalValidator::class],
      [['id', 'sync_updated_at'], 'safe']
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'name' => Yii::_t('promo.countries.attribute-name'),
      'status' => Yii::_t('promo.countries.attribute-status'),
      'code' => Yii::_t('promo.countries.attribute-code'),
      'created_at' => Yii::_t('promo.countries.attribute-created_at'),
      'updated_at' => Yii::_t('promo.countries.attribute-updated_at'),
      'currency' => Yii::_t('promo.countries.attribute-currency'),
      'local_currency' => Yii::_t('promo.countries.attribute-currency'),
    ];
  }

  public function afterSave($insert, $changedAttributes)
  {
    $insert
      ? (new CountryCreated($this))->trigger()
      : (new CountryUpdated($this))->trigger();

    $this->invalidateCache($insert, $changedAttributes);

    $this->updateSets();

    parent::afterSave($insert, $changedAttributes);
  }

  public function invalidateCache($insert, $changedAttributes)
  {
    TagDependency::invalidate(Yii::$app->cache, ['country']);

    if ($insert || ArrayHelper::getValue($changedAttributes, 'status')) {
      ApiHandlersHelper::clearCache('OperatorsIps'); // Сбрасываем кэш микросервисов
    }
    ApiHandlersHelper::clearCache('OperatorsData');
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getOperator()
  {
    return $this->hasMany(Operator::class, ['country_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getOperators()
  {
    return $this->hasMany(Operator::class, ['country_id' => 'id']);
  }

  public function getActiveOperator()
  {
    return $this
      ->hasMany(Operator::class, ['country_id' => 'id'])
      ->andWhere([Operator::tableName() . '.' . 'status' => Operator::STATUS_ACTIVE]);
  }

  public function getActiveOperatorCount()
  {
    return $this->getActiveOperator()->count();
  }

  public function getStatuses($status = null)
  {
    $list = [
      self::STATUS_INACTIVE => Yii::_t('promo.countries.status-inactive'),
      self::STATUS_ACTIVE => Yii::_t('promo.countries.status-active')
    ];
    return isset($status) ? ArrayHelper::getValue($list, $status, null) : $list;
  }


  public function getCurrentStatusName()
  {
    return $this->getStatuses($this->status);
  }

  /**
   * @return integer
   */
  public function getLandingsCount()
  {
    if ($this->_landingsCount === null) {
      $this->_landingsCount = 0;
      foreach ($this->operator as $operator) {
        $this->_landingsCount += $operator->landingsCount;
      }
    }

    return $this->_landingsCount;
  }

  /**
   * @param bool $isSelectUnlockedHidden TRICKY: Показывать ли ленды, у которых доступ=скрытый,
   * но у партнера есть доступ по заявке
   * @param bool $clearCache KOSTYL: Удаление кэша данного параметра (эта модель у нас кэшируется, поэтому надо очищать приватный параметр вручную)
   * @return int
   */
  public function getActiveLandingsCount($isSelectUnlockedHidden = false, $clearCache = false)
  {
    $this->_activeLandingsCount = !$clearCache ? $this->_activeLandingsCount : null;

    if ($this->_activeLandingsCount === null) {
      $this->_activeLandingsCount = 0;
      foreach ($this->activeOperator as $operator) {
        if (!$operator->isTrafficBlocked()) {
          $this->_activeLandingsCount += $operator->getActiveLandingsCount($isSelectUnlockedHidden);
        }
      }
    }

    return $this->_activeLandingsCount;
  }

  public function getReplacements()
  {
    return [
      'id' => [
        'value' => $this->isNewRecord ? null : $this->id,
        'help' => [
          'label' => Yii::_t('promo.replacements.country_id')
        ]
      ],
      'name' => [
        'value' => $this->isNewRecord ? null : $this->name,
        'help' => [
          'label' => Yii::_t('promo.replacements.country_name')
        ]
      ],
      'status' => [
        'value' => $this->isNewRecord ? null : $this->getCurrentStatusName(),
        'help' => [
          'label' => Yii::_t('promo.replacements.country_status')
        ]
      ],
      'code' => [
        'value' => $this->isNewRecord ? null : $this->code,
        'help' => [
          'label' => Yii::_t('promo.replacements.country_code')
        ]
      ]
    ];
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

  public function getOperatorsLink()
  {
    return Link::get(
      '/promo/operators/index',
      [(new OperatorSearch())->formName() . '[country_id]' => $this->id],
      ['data-pjax' => 0],
      Yii::_t('promo.operators.main')) . ' ' . Html::tag('span', $this->activeOperatorCount, ['class' => 'label label-default']);
  }

  public static function getViewUrl($id, $asString = false)
  {
    $arr = ['/promo/countries/view', 'id' => $id];
    return $asString ? Url::to($arr) : $arr;
  }


  /**
   * @param bool $isDisabled
   * @return string
   */
  public function getViewLink($isDisabled = false)
  {
    return \mcms\common\helpers\Link::get(
      '/promo/countries/view',
      ['id' => $this->id],
      [
        'data-pjax' => 0,
        'class' => $this->status == self::STATUS_ACTIVE && !$isDisabled ? '' : 'text-danger'
      ], $this->getStringInfo(), false
    );
  }

  public function getStringInfo()
  {
    return sprintf(
      '#%s - %s',
      ArrayHelper::getValue($this, 'id'),
      ArrayHelper::getValue($this, 'name')
    );
  }

  /**
   * @return boolean
   */
  public function isActive()
  {
    return $this->status == self::STATUS_ACTIVE;
  }

  /**
   * Получение стран для выпадающего списка
   * @param int $status
   * @return array
   */
  public static function getDropdownItems($status = null)
  {
    $query = static::find()->orderBy('name');
    if ($status !== null) $query->andWhere(['status' => $status]);
    return ArrayHelper::map($query->each(), 'id', 'name');
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public static function getInactiveCountryQuery()
  {
    return static::find()
      ->where(['<>', 'status', self::STATUS_ACTIVE])
      ;
  }

  public static function getActiveWithActiveLandings()
  {
    return self::find()
      ->asArray()
      ->select([
        self::tableName() . '.id',
        self::tableName() . '.code',
        self::tableName() . '.name',
      ])
      ->indexBy('id')
      ->innerJoinWith('activeOperator.activeLandings', false)
      ->orderBy(self::tableName() . '.name')
      ->all();
  }

  private function updateSets()
  {
    $landingIds = [];

    foreach ($this->getOperator()->all() as $operator) {
      /** @var Operator $operator */
      $landingIds = array_merge(
        $landingIds,
        ArrayHelper::getColumn($operator->getLanding()->all(), 'id')
        );
    }

    (new LandingSetsLandsUpdater(['landingIds' => array_unique($landingIds)]))->run();
  }

  /**
   * @param $id
   * @return $this|null
   */
  public static function findOneCached($id)
  {
    if (self::$_countries === null) {
      self::$_countries = self::find()->indexBy('id')->all();
    }

    return ArrayHelper::getValue(self::$_countries, $id);
  }

  /**
   * Получает количество активных операторов у которых есть лендинги
   * @return int|string
   */
  public function getActiveOperatorWithLandingsCount()
  {
    $count = 0;
    $country = clone $this;
    foreach ($country->getActiveOperator()->each() as $operator) {
      if($operator->getActiveLandingsCount(true) > 0 && !$operator->isTrafficBlocked()) {
        $count++;
      }
    }

    return $count;
  }
}
