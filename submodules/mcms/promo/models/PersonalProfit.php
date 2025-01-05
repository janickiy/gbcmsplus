<?php

namespace mcms\promo\models;

use mcms\common\traits\Translate;
use mcms\promo\components\api\GetPersonalProfit;
use mcms\promo\components\events\personal_profit\PartnerLandingChanged;
use mcms\promo\components\events\personal_profit\PartnerOperatorChanged;
use mcms\promo\components\events\personal_profit\PartnerPersonalChanged;
use mcms\promo\components\events\personal_profit\ResellerPersonalChanged;
use mcms\promo\components\UsersHelper;
use mcms\promo\models\search\LandingSearch;
use mcms\promo\Module;
use Yii;
use mcms\user\models\User;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%personal_profit}}".
 *
 * @property integer $user_id
 * @property integer $operator_id
 * @property integer $landing_id
 * @property string $rebill_percent
 * @property string $buyout_percent
 * @property string $cpa_profit_rub
 * @property string $cpa_profit_eur
 * @property string $cpa_profit_usd
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $country_id
 * @property integer $provider_id
 *
 * @property User $createdBy
 * @property Landing $landing
 * @property Operator $operator
 * @property Provider $provider
 * @property Country $country
 * @property User $user
 */
class PersonalProfit extends \yii\db\ActiveRecord
{

  use Translate;

  const LANG_PREFIX = 'promo.personal-profits.';

  const CACHE_PREFIX = 'personal_profit_ar__';
  const CACHE_KEY_EMPTY_USER = self::CACHE_PREFIX . 'empty_user';

  const GLOBAL_CACHE_TAG = self::CACHE_PREFIX . 'global';

  const MAX_REBILL_PERCENT = 100;

  private static $_canManagePersonalCPAPrice;

  /**
   * Категория лендинга.
   * Самого поля для этого нет, но это нужно для того, чтоб создать сразу несколько условий доходности
   * @var int
   */
  public $landingCategory;

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
    return '{{%personal_profit}}';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['created_by', 'default', 'value' => isset(Yii::$app->user) ? Yii::$app->user->id : null],
      [['created_by'], 'required'],
      [['user_id', 'operator_id', 'landing_id', 'created_by', 'provider_id', 'country_id'], 'integer'],
      ['rebill_percent', 'number', 'max' => self::MAX_REBILL_PERCENT],
      ['buyout_percent', 'number'],
      [['rebill_percent'], 'checkAtLeastOneRequired', 'skipOnEmpty' => false],
      [['cpa_profit_rub', 'cpa_profit_eur', 'cpa_profit_usd'], 'number'],
      [['user_id'], 'safe'],
      ['user_id', 'required', 'message' => self::translate('user_id_required_error'), 'when' => function ($model) {
        return !empty($model->cpa_profit_rub) || !empty($model->cpa_profit_usd) || !empty($model->cpa_profit_eur);
      }],
      [['user_id', 'operator_id', 'landing_id', 'provider_id', 'country_id'], 'filter', 'filter' => 'intval'],
      ['operator_id', 'unique', 'targetAttribute' => ['operator_id', 'landing_id', 'user_id', 'provider_id', 'country_id'], 'message' => self::translate('unique_validate_fail')],
      [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
      [
        ['landing_id'],
        'exist',
        'skipOnError' => true,
        'targetClass' => Landing::class,
        'targetAttribute' => ['landing_id' => 'id'],
        'when' => function ($model) {
          return (bool)$model->landing_id;
        }
      ],
      [
        ['operator_id'],
        'exist',
        'skipOnError' => true,
        'targetClass' => Operator::class,
        'targetAttribute' => ['operator_id' => 'id'],
        'when' => function ($model) {
          return (bool)$model->operator_id;
        }
      ],
      [
        ['provider_id'],
        'exist',
        'skipOnError' => true,
        'targetClass' => Provider::class,
        'targetAttribute' => ['provider_id' => 'id'],
        'when' => function ($model) {
          return (bool)$model->provider_id;
        }
      ],
      [
        ['country_id'],
        'exist',
        'skipOnError' => true,
        'targetClass' => Country::class,
        'targetAttribute' => ['country_id' => 'id'],
        'when' => function ($model) {
          return (bool)$model->country_id;
        }
      ],
      [
        ['user_id'],
        'exist',
        'skipOnError' => true,
        'targetClass' => User::class,
        'targetAttribute' => ['user_id' => 'id'],
        'when' => function ($model) {
          return (bool)$model->user_id;
        }
      ],
      ['landingCategory', 'integer'],
    ];
  }

  /**
   * @return bool
   */
  public function beforeValidate()
  {
    if ($this->user_id != Yii::$app->user->id)
      return parent::beforeValidate();

    foreach ($this->getDeniedEditingOwnAttributes() as $attribute) {
      if ($this->{$attribute} == $this->getOldAttribute($attribute)) continue;
      $this->addError($attribute, self::translate('deny_edit_own'));
    }
    return parent::beforeValidate();
  }

  /**
   * @return array
   */
  public function getDeniedEditingOwnAttributes()
  {
    return ['rebill_percent'];
  }

  /**
   * @param $attribute
   * @return bool
   */
  public function checkAtLeastOneRequired($attribute)
  {
    if (
      !empty($this->rebill_percent)
      || !empty($this->buyout_percent)
      || !empty($this->cpa_profit_rub)
      || !empty($this->cpa_profit_eur)
      || !empty($this->cpa_profit_usd)
    ) {
      return true;
    }

    if (!self::canManagePersonalCPAPrice() && in_array($attribute, ['cpa_profit_rub', 'cpa_profit_eur', 'cpa_profit_usd'])) return true;

    $this->addError('rebill_percent', self::translate('at_least_one_required_validate_fail'));
    $this->addError('buyout_percent', self::translate('at_least_one_required_validate_fail'));
    $this->addError('cpa_profit_rub', self::translate('at_least_one_required_validate_fail'));
    $this->addError('cpa_profit_eur', self::translate('at_least_one_required_validate_fail'));
    $this->addError('cpa_profit_usd', self::translate('at_least_one_required_validate_fail'));
    return false;
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'user_id' => self::translate('attribute-user_id'),
      'operator_id' => self::translate('attribute-operator_id'),
      'landing_id' => self::translate('attribute-landing_id'),
      'provider_id' => self::translate('attribute-provider_id'),
      'country_id' => self::translate('attribute-country_id'),
      'rebill_percent' => self::translate('attribute-rebill_percent'),
      'buyout_percent' => self::translate('attribute-buyout_percent'),
      'cpa_profit' => self::translate('attribute-cpa_profit'),
      'cpa_profit_rub' => self::translate('attribute-cpa_profit_rub'),
      'cpa_profit_eur' => self::translate('attribute-cpa_profit_eur'),
      'cpa_profit_usd' => self::translate('attribute-cpa_profit_usd'),
      'landingCategory' => self::translate('attribute-landing-category'),
      'created_by' => 'Created By',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
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
  public function getLanding()
  {
    return $this->hasOne(Landing::class, ['id' => 'landing_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getOperator()
  {
    return $this->hasOne(Operator::class, ['id' => 'operator_id']);
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
  public function getCountry()
  {
    return $this->hasOne(Country::class, ['id' => 'country_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }

  /**
   * @return null|string
   */
  public function getUserLink()
  {
    return $this->user ? $this->user->getViewLink() : null;
  }

  /**
   * @return null|string
   */
  public function getLandingLink()
  {
    return $this->landing ? $this->landing->getViewLink() : null;
  }

  /**
   * @return null|string
   */
  public function getProviderLink()
  {
    return $this->provider ? $this->provider->getViewLink() : null;
  }

  /**
   * @return null|string
   */
  public function getCountryLink()
  {
    return $this->country ? $this->country->getViewLink() : null;
  }

  /**
   * @return null|string
   */
  public function getOperatorLink()
  {
    return $this->operator ? $this->operator->getViewLink() : null;
  }

  public function save($runValidation = true, $attributeNames = null)
  {
    if (empty($this->landingCategory) || !empty($this->landing_id)) {
      return parent::save($runValidation, $attributeNames);
    }

    $landingSearch = new LandingSearch();
    $dataProvider = $landingSearch->search([
      'category_id' => $this->landingCategory,
      'provider_id' => $this->provider_id,
      'operator_id' => $this->operator_id,
    ], '');

    $query = $dataProvider->query;

    $transaction = Yii::$app->getDb()->beginTransaction();
    try {
      foreach ($query->asArray()->each() as $model) {
        $personalProfitModel = clone $this;
        $personalProfitModel->landingCategory = null;
        $personalProfitModel->landing_id = $model['id'];
        $personalProfitModel->save();
      }
      $transaction->commit();
      return true;
    } catch (\Exception $e) {
      $transaction->rollBack();
      return false;
    }
  }


  /**
   * @param bool $insert
   * @param array $changedAttributes
   */
  public function afterSave($insert, $changedAttributes)
  {
    $roles = $this->user_id ? UsersHelper::getRolesByUserId($this->user_id) : [];

    if (in_array('partner', $roles)) {
      if (!$this->landing_id && !$this->operator_id) {
        (new PartnerPersonalChanged($this))->trigger();
      }
      if ($this->landing_id) {
        (new PartnerLandingChanged($this))->trigger();
      }
      if ($this->operator_id) {
        (new PartnerOperatorChanged($this))->trigger();
      }
    }

    if (in_array('reseller', $roles)) {
      (new ResellerPersonalChanged($this))->trigger();
    }

    $this->invalidateCache();

    parent::afterSave($insert, $changedAttributes);
  }


  /**
   * @return array
   */
  public function getReplacements()
  {
    /** @var Operator $operator */
    $operator = $this->getOperator()->one();

    /** @var Landing $landing */
    $landing = $this->getLanding()->one();

    /** @var User $user */
    $user = $this->getUser()->one();

    /** @var User $createdBy */
    $createdBy = $this->getCreatedBy()->one();
    return [
      'user' => [
        'value' => $this->isNewRecord ? null : $user->getReplacements(),
        'help' => [
          'label' => self::translate('replacements-user')
        ]
      ],
      'operator' => [
        'value' => $this->isNewRecord || !$operator ? null : $operator->getReplacements(),
        'help' => [
          'label' => self::translate('replacements-operator')
        ]
      ],
      'landing' => [
        'value' => $this->isNewRecord || !$landing ? null : $landing->getReplacements(),
        'help' => [
          'label' => self::translate('replacements-landing')
        ]
      ],
      'createdBy' => [
        'value' => $this->isNewRecord ? null : $createdBy->getReplacements(),
        'help' => [
          'label' => self::translate('replacements-createdBy')
        ]
      ],
      'rebillPercent' => [
        'value' => $this->isNewRecord ? null : $this->rebill_percent,
        'help' => [
          'label' => self::translate('replacements-rebillPercent')
        ]
      ],
      'buyoutPercent' => [
        'value' => $this->isNewRecord ? null : $this->buyout_percent,
        'help' => [
          'label' => self::translate('replacements-buyoutPercent')
        ]
      ],
      'cpaProfitRub' => [
        'value' => $this->isNewRecord ? null : $this->cpa_profit_rub,
        'help' => [
          'label' => self::translate('replacements-cpaProfitRub')
        ]
      ],
      'cpaProfitEur' => [
        'value' => $this->isNewRecord ? null : $this->cpa_profit_eur,
        'help' => [
          'label' => self::translate('replacements-cpaProfitEur')
        ]
      ],
      'cpaProfitUsd' => [
        'value' => $this->isNewRecord ? null : $this->cpa_profit_usd,
        'help' => [
          'label' => self::translate('replacements-cpaProfitUsd')
        ]
      ],
    ];
  }

  public function afterDelete()
  {
    $this->invalidateCache();
    parent::afterDelete();
  }

  /**
   * Данный кэш создается и используется в апи GetPersonalProfit.
   * При изменении текущей модели необходимо сбрасывать кэш для юзера.
   */
  public function invalidateCache()
  {
    TagDependency::invalidate(Yii::$app->cache, [
      GetPersonalProfit::CACHE_KEY_PREFIX . 'module_percents',
      self::GLOBAL_CACHE_TAG
    ]);
  }

  /**
   * Проверяем может ли текущий пользователь редактировать и смотреть персональные цены за выкуп.
   * В промо есть настройка "Разрешить реселлеру задавать инд. цены за CPA"
   * @return bool
   */
  public static function canManagePersonalCPAPrice()
  {
    if (self::$_canManagePersonalCPAPrice !== null) return self::$_canManagePersonalCPAPrice;

    if (Yii::$app->user->can(Module::PERMISSION_PROMO_MANAGE_PERSONAL_CPA_PRICE)) {
      return self::$_canManagePersonalCPAPrice = true;
    }

    if (Yii::$app->user->can(Module::PERMISSION_PROMO_MANAGE_PERSONAL_CPA_PRICE_IF_ENABLED)) {
      return self::$_canManagePersonalCPAPrice = true;
    }

    return self::$_canManagePersonalCPAPrice = false;
  }


  /**
   * @param $userId
   * @return static[]
   */
  public static function getModelsByUser($userId)
  {
    $key = self::getModelsByUserCacheKey($userId);
    $result = Yii::$app->cache->get($key);

    if ($result !== false) {
      return $result;
    }

    $result = self::findAll(['user_id' => $userId]);

    $cacheDependency = new TagDependency(['tags' => [$key, self::GLOBAL_CACHE_TAG]]);

    Yii::$app->cache->set($key, $result, 3600, $cacheDependency);

    return $result;
  }

  /**
   * @return static[]
   */
  public static function getModelsEmptyUser()
  {
    $key = self::CACHE_KEY_EMPTY_USER;

    $result = Yii::$app->cache->get($key);

    if ($result !== false) {
      return $result;
    }

    $result = self::findAll(['user_id' => 0]);

    $cacheDependency = new TagDependency(['tags' => [self::GLOBAL_CACHE_TAG]]);

    Yii::$app->cache->set($key, $result, 3600, $cacheDependency);

    return $result;
  }

  /**
   * @param $userId
   * @return string
   */
  private static function getModelsByUserCacheKey($userId)
  {
    return self::CACHE_PREFIX . $userId;
  }
}
