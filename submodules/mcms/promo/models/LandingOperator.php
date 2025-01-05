<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\promo\components\api\GetPersonalProfit;
use mcms\promo\components\api\MainCurrencies;
use mcms\promo\components\LandingOperatorCompletePrices;
use mcms\currency\models\Currency as CurrencyModel;
use rgk\utils\behaviors\TimestampBehavior;
use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%landing_operators}}".
 *
 * @property integer $landing_id
 * @property integer $operator_id
 * @property integer $days_hold
 * @property integer $default_currency_id
 * @property integer $local_currency_id
 * @property string $default_currency_rebill_price
 * @property string $local_currency_rebill_price
 * @property string $buyout_price_usd
 * @property string $buyout_price_eur
 * @property string $buyout_price_rub
 * @property string $rebill_price_usd
 * @property string $rebill_price_eur
 * @property string $rebill_price_rub
 * @property string $cost_price
 * @property integer $subscription_type_id
 * @property integer $use_landing_operator_rebill_price
 * @property integer $is_deleted
 *
 * @property Currency $defaultCurrency
 * @property Currency $localCurrency
 * @property Landing $landing
 * @property Operator $operator
 * @property LandingPayType[] $payTypes
 * @property LandingPayType[] $activePayTypes
 * @property bool $isOnetime
 */
class LandingOperator extends \yii\db\ActiveRecord
{
  const TABLE_OPERATOR_PAY_TYPES = '{{%landing_operator_pay_types}}';
  //TRICKY дефолтная валюта всегда сейчас в лендах евро
  const DEFAULT_CURRENCY_ID = 3;

  public $payTypeIds = [];

  protected $userProfit;
  /** @var LandingOperatorCompletePrices */
  protected $completePrices;

  private $completedCurrencyValues = [];

  private static $subTypes;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%landing_operators}}';
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
  public function rules()
  {
    return [
      [['days_hold', 'buyout_price_usd', 'buyout_price_eur', 'buyout_price_rub'], 'default', 'value' => 0, 'skipOnEmpty' => false],
      [['landing_id', 'operator_id', 'local_currency_id',
        'local_currency_rebill_price','subscription_type_id'], 'required'],
      [['landing_id', 'operator_id', 'days_hold', 'default_currency_id', 'subscription_type_id', 'is_deleted', 'use_landing_operator_rebill_price'], 'integer'],
      [['default_currency_rebill_price', 'local_currency_rebill_price', 'buyout_price_usd', 'buyout_price_eur', 'buyout_price_rub', 'rebill_price_usd', 'rebill_price_eur', 'rebill_price_rub'], 'number', 'skipOnEmpty' => true, 'min' => 0],
      [['operator_id', 'landing_id'], 'unique', 'targetAttribute' => ['operator_id', 'landing_id'], 'message' => 'The combination of Landing ID, Operator ID has already been taken.'],
      [['default_currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => CurrencyModel::class, 'targetAttribute' => ['default_currency_id' => 'id']],
      [['local_currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => CurrencyModel::class, 'targetAttribute' => ['local_currency_id' => 'id']],
      [['landing_id'], 'exist', 'skipOnError' => true, 'targetClass' => Landing::class, 'targetAttribute' => ['landing_id' => 'id']],
      [['operator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Operator::class, 'targetAttribute' => ['operator_id' => 'id']],
      [['days_hold'], 'compare', 'compareValue' => 0, 'operator' => '>=', 'type' => 'number'],
      [['payTypeIds', 'cost_price'], 'safe'],
      ['default_currency_id', 'default', 'value' => self::DEFAULT_CURRENCY_ID],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'landing_id' => Yii::_t('promo.landings.operator-attribute-landing_id'),
      'operator_id' => Yii::_t('promo.landings.operator-attribute-operator_id'),
      'days_hold' => Yii::_t('promo.landings.operator-attribute-days_hold'),
      'default_currency_id' => Yii::_t('promo.landings.operator-attribute-default_currency_id'),
      'default_currency_rebill_price' => Yii::_t('promo.landings.operator-attribute-default_currency_rebill_price'),
      'local_currency_id' => Yii::_t('promo.landings.operator-attribute-local_currency_id'),
      'local_currency_rebill_price' => Yii::_t('promo.landings.operator-attribute-local_currency_rebill_price'),
      'buyout_price_usd' => Yii::_t('promo.landings.operator-attribute-buyout_price_usd'),
      'buyout_price_eur' => Yii::_t('promo.landings.operator-attribute-buyout_price_eur'),
      'buyout_price_rub' => Yii::_t('promo.landings.operator-attribute-buyout_price_rub'),
      'rebill_price_usd' => Yii::_t('promo.landings.operator-attribute-rebill_price_usd'),
      'rebill_price_eur' => Yii::_t('promo.landings.operator-attribute-rebill_price_eur'),
      'rebill_price_rub' => Yii::_t('promo.landings.operator-attribute-rebill_price_rub'),
      'subscription_type_id' => Yii::_t('promo.landings.operator-attribute-subscription_type_id'),
      'payTypeIds' => Yii::_t('promo.landings.operator-attribute-payTypeIds'),
      'cost_price' => Yii::_t('promo.landings.operator-attribute-cost_price'),
      'is_deleted' => Yii::_t('promo.landings.operator-attribute-is_deleted'),
      'use_landing_operator_rebill_price' => Yii::_t('promo.landings.operator-attribute-use_landing_operator_rebill_price'),
    ];
  }

  /**
   * @param bool $insert
   * @return bool
   */
  public function beforeSave($insert)
  {
    if (!$this->default_currency_rebill_price) {
      $this->default_currency_rebill_price = PartnerCurrenciesProvider::getInstance()
        ->getCurrencies()
        ->getCurrencyById($this->local_currency_id)
        ->convertToEur($this->local_currency_rebill_price);
    }
    return parent::beforeSave($insert);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getDefaultCurrency()
  {
    return $this->hasOne(CurrencyModel::class, ['id' => 'default_currency_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLocalCurrency()
  {
    return $this->hasOne(CurrencyModel::class, ['id' => 'local_currency_id']);
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
  public function getActiveLanding()
  {
    return $this->hasOne(Landing::class, ['id' => 'landing_id'])
      ->where([Landing::tableName() . '.status' => Landing::STATUS_ACTIVE]);
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
  public function getActiveOperator()
  {
    return $this->hasOne(Operator::class, ['id' => 'operator_id'])
      ->where([Operator::tableName() . '.status' => Operator::STATUS_ACTIVE]);
  }

  public function getLandingOperatorPayTypes()
  {
    return $this->hasMany(LandingOperatorPayType::class, ['landing_id' => 'landing_id', 'operator_id' => 'operator_id']);
  }

  public function getPayTypes()
  {
    return $this->hasMany(LandingPayType::class, ['id' => 'landing_pay_type_id'])
      ->via('landingOperatorPayTypes');
  }

  public function getActivePayTypes()
  {
    return $this->hasMany(LandingPayType::class, ['id' => 'landing_pay_type_id'])
      ->via('landingOperatorPayTypes')
      ->where(['=', LandingPayType::tableName() . '.status', LandingPayType::STATUS_ACTIVE]);
  }

  static public function findOrCreateModel($landing_id, $operator_id)
  {
    return $landing_id && $operator_id && ($model = self::findOne([
      'landing_id' => $landing_id,
      'operator_id' => $operator_id])
    ) ? $model : new self([
      'landing_id' => $landing_id,
      'operator_id' => $operator_id
    ]);
  }

  public function getOperators($activeOnly = true)
  {
    $operators = Operator::find()->orderBy('name');
    if ($activeOnly) $operators->where('status = :active_status', [':active_status' => Operator::STATUS_ACTIVE]);
    return ArrayHelper::map($operators->each(), 'id', 'name');
  }

  public static function getOperatorsByLanding($landingId, $activeOnly = true)
  {
    $result = [];
    $operators = self::find()->joinWith('operator')->orderBy('name');
    $operators->where('landing_id = :landing_id', [':landing_id' => $landingId]);
    if ($activeOnly) $operators->andWhere('operators.status = :active_status', [':active_status' => Operator::STATUS_ACTIVE]);
    foreach($operators->each() as $operator) {
      $result[] = ['id' => $operator->operator_id, 'name' => $operator->operator->name];
    }
    return $result;
  }

  public function getCurrencies()
  {
    return ArrayHelper::map(CurrencyModel::find()->orderBy('name')->orderBy('id')->each(), 'id', 'name');
  }

  /**
     * @return \yii\db\ActiveQuery
     */
  public function getSubscriptionType()
  {
    return $this->hasOne(LandingSubscriptionType::class, ['id' => 'subscription_type_id']);
  }


  public function afterSave($insert, $changedAttributes)
  {
    if (!empty($this->payTypeIds)) {
      $this->saveOrDeletePayTypes($insert);
    }

    $this->invalidateCache();

    parent::afterSave($insert, $changedAttributes);
  }

  public function invalidateCache()
  {
    TagDependency::invalidate(Yii::$app->cache, ['landing']);
  }

  public function afterDelete()
  {
    $this->invalidateCache();
    parent::afterDelete();
  }

  /**
   * @param $insert
   */
  protected function saveOrDeletePayTypes($insert)
  {
    $oldPayTypes = $insert ? [] : $this->payTypes;

    /**
     * добавляем новые связи
     */
    $this->payTypeIds = empty($this->payTypeIds) ? [] : $this->payTypeIds;

    foreach ($this->payTypeIds as $payTypeId) {
      if (
        !in_array($payTypeId, ArrayHelper::getColumn($oldPayTypes, 'id')) &&
        $payTypeModel = LandingPayType::findOne($payTypeId)
      ) {
        $this->link('payTypes', $payTypeModel);
      }
    }

    /**
     * удаляем старые связи
     */
    foreach($oldPayTypes as $oldPayType) {
      if (!in_array($oldPayType->id, $this->payTypeIds)) {
        $this->unlink('payTypes', $oldPayType, true);
      }
    }
  }

  /**
   * Строка в виде: - 81,00 руб. / 14,00 руб.
   * В валюте ленд-оператора
   * @return string
   */
  public function getConvertedBuyOutRebillString()
  {
    // MainCurrencies если что кэшируется
    $currency = '';
    foreach ((new MainCurrencies())->getResult() as $promoCurrency) {
      if ($this->local_currency_id == $promoCurrency['id']) {
        $currency = $promoCurrency['code'];
      }
    }

    return sprintf(
      ' - %s / %s',
      Yii::$app->formatter->asLandingPrice($this->getCompletePrices()->getBuyoutPrice($currency), $currency),
      Yii::$app->formatter->asLandingPrice($this->getCompletePrices()->getRebillPrice($currency), $currency)
    );
  }

  /**
   * @return LandingOperatorCompletePrices
   */
  public function getCompletePrices()
  {
    if ($this->completePrices) {
      return $this->completePrices;
    }
    $this->completePrices = LandingOperatorCompletePrices::create($this);
    return $this->completePrices;
  }

  public function canUpdateBuyoutProfit()
  {
    return Yii::$app->user->can('PromoLandingsUpdateBuyoutProfit');
  }

  /**
   * @param array $completedCurrencyValues
   */
  public function setCompletedCurrencyValues(array $completedCurrencyValues)
  {
    $this->completedCurrencyValues = array_merge($this->completedCurrencyValues, $completedCurrencyValues);
  }

  public function getOperatorLink()
  {
    return $this->operator->getViewLink();
  }

  public function getCurrencyLink()
  {
    return $this->defaultCurrency->getViewLink();
  }

  public function getIsOnetime()
  {
    if (empty(self::$subTypes)) {
      self::$subTypes = ArrayHelper::map(LandingSubscriptionType::find()->each(), 'code', 'id');
    }

    return $this->subscription_type_id == ArrayHelper::getValue(self::$subTypes, LandingSubscriptionType::CODE_ONETIME);
  }

  /**
   * в той валюте в которой лендинг сам
   * если валюта не eur rub usd то выкупаем в той которая задана в приоритете  rub, usd, eur
   * @return array|false
   */
  public function getBuyoutCurrency()
  {
    /** @var array $mainCurrencies */
    $mainCurrencies = (new MainCurrencies())->getResult();
    foreach ($mainCurrencies as $mainCurrency) {
      if ($this->default_currency_id == $mainCurrency['id']) {
        return $mainCurrency;
      }
    }

    foreach (['rub', 'usd', 'eur'] as $currencyCode) {
      $param = 'buyout_price_' . $currencyCode;
      if ($this->{$param}) {
        return current(array_filter($mainCurrencies, function ($currency) use ($currencyCode, $mainCurrencies) {
          return $currencyCode == $mainCurrencies['code'];
        }));
      }
    }
    return false;
  }

  public function getPayTypesNameText()
  {
    return implode(', ', array_map(function ($payType) {
      return $payType->name;
    }, $this->payTypes));
  }

  /**
   * @return ActiveQuery
   */
  public static function findActiveLandingOperators()
  {
    return self::find()->andWhere(['is_deleted' => 0])->joinWith('activeLanding', true, 'INNER JOIN')->joinWith('activeOperator', true, 'INNER JOIN');
  }

  public static function findActivePayTypes($landingOoperatorIds = [])
  {
    $query = self::find()
      ->with('activePayTypes');
    $landingOperatorCondition = [];
    foreach ($landingOoperatorIds as $landingOperator) {
      $landingOperatorCondition[] = [self::tableName() . '.landing_id' => $landingOperator['landing_id'], self::tableName() . '.operator_id' => $landingOperator['operator_id']];
    }
    $query->where(['in', [self::tableName() . '.landing_id', self::tableName() . '.operator_id'], $landingOperatorCondition]);
    return $query->all();
  }
}
