<?php


namespace mcms\promo\components\api;


use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\promo\components\api\personal_profit\PersonalProfitValues;
use mcms\promo\models\Landing;
use mcms\promo\models\Operator;
use mcms\promo\models\PersonalProfit;
use mcms\promo\models\search\PersonalProfitSearch;
use mcms\promo\Module;
use Yii;
use yii\caching\TagDependency;

/**
 * Class GetPersonalProfit
 * @package mcms\promo\components\api
 */
class GetPersonalProfit extends ApiResult
{

  /** @var int */
  protected $userId;
  /** @var int */
  protected $operatorId;
  /** @var int */
  protected $landingId;
  /** @var int */
  protected $countryId;
  /** @var int */
  protected $providerId;
  /** @var string */
  protected $userType;
  /** @var Module */
  protected $module;
  /** @var \mcms\user\Module */
  protected $userModule;
  /** @var string */
  protected $cacheKey;
  /** @var array */
  protected $cacheTags;
  /** @var bool */
  protected $canUseNotPersonalPercent = false;

  const USER_TYPE_PARTNER = 'partner';
  const USER_TYPE_RESELLER = 'reseller';
  const USER_TYPE_ROOT = 'root';
  const DEFAULT_USER_TYPE = self::USER_TYPE_PARTNER;

  const REBILL_PARAM = 'rebill_percent';
  const BUYOUT_PARAM = 'buyout_percent';
  const CPA_PROFIT_PARAM_RUB = 'cpa_profit_rub';
  const CPA_PROFIT_PARAM_EUR = 'cpa_profit_eur';
  const CPA_PROFIT_PARAM_USD = 'cpa_profit_usd';
  const UPDATED_AT_PARAM = 'updated_at';

  const RESELLER_BUYOUT_PERCENT = 100;
  const RESELLER_REBILL_PERCENT = 100;

  const CACHE_KEY_PREFIX = 'personal_profit__';

  private static $operatorModelCache = [];

  /**
   * @inheritdoc
   */
  public function init($params = [])
  {
    $this->userId = ArrayHelper::getValue($params, 'userId');
    $this->operatorId = ArrayHelper::getValue($params, 'operatorId');
    $this->landingId = ArrayHelper::getValue($params, 'landingId');
    $this->providerId = ArrayHelper::getValue($params, 'providerId');

    if (!$this->userId) $this->addError('userId is not set');

    $authManager = Yii::$app->authManager;

    $this->userType = self::DEFAULT_USER_TYPE;

    if ($authManager->checkAccess($this->userId, Module::PERMISSION_APPLY_PERSONAL_PERCENT_AS_RESELLER)) {
      $this->userType = self::USER_TYPE_RESELLER;
    } elseif ($authManager->checkAccess($this->userId, Module::PERMISSION_APPLY_PERSONAL_PERCENT_AS_ROOT)) {
      $this->userType = self::USER_TYPE_ROOT;
    }

    $this->module = Yii::$app->getModule('promo');
    $this->userModule = Yii::$app->getModule('users');

    $this->cacheKey = self::CACHE_KEY_PREFIX . sprintf(
        'user%s-landing%s-operator%s',
        $this->userId,
        $this->landingId,
        $this->operatorId
      );

    $this->cacheTags = [
      self::CACHE_KEY_PREFIX . 'userid' . $this->userId,
      self::CACHE_KEY_PREFIX . 'module_percents'
    ];

    $this->canUseNotPersonalPercent = $authManager->checkAccess($this->userId, Module::PERMISSION_USE_NOT_PERSONAL_PERCENT);
  }

  /**
   * @inheritdoc
   */
  public function getResult()
  {
    if (!$this->userId) return false;

    return $this->getPersonalPercents();
  }

  /**
   * @return \yii\data\ActiveDataProvider
   */
  public function getAllResults()
  {
    $dataProvider = (new PersonalProfitSearch([
      'user_id' => $this->userId
    ]))->search([]);

    $dataProvider->setPagination(false);

    return $dataProvider;
  }

  /**
   * @return array
   */
  public function getPersonalPercents()
  {
    $result = Yii::$app->cache->get($this->cacheKey);

    if ($result) return $result;

    // If cache value NOT exist
    $result = $this->getPersonalPercentsByAttributes();

    /**
     * В разделе промо для реселлера
     * Реселлер в промо должен видеть цены за ребиллы с учетом % реселлера (который можно менять)
     * Реселлер видит цену за выкуп = цене за выкуп выставленную админом

     * И в партнерском кабинете при выборе лендингов для партнера
     * Партнер видит цену за ребил с учетом % инвестора и % партнера
     * Партнер видит цену за выкуп = цене выкупа подписки админом с учетом выставленного пользователю %
     */

    // Put result to cache
    $cacheDependency = new TagDependency(['tags' => $this->cacheTags]);
    Yii::$app->cache->set($this->cacheKey, $result, 3600, $cacheDependency);

    return $result;
  }

  /**
   * @return array
   */
  protected function getPersonalPercentsByAttributes()
  {
    // методом исключения пробуем найти подходящую модель персональных профитов
    // начинаем от самых узких и постепенно расширяемся до более общих
    $profitValues = new PersonalProfitValues();

    // по юзеру, лендингу, оператору
    if ($this->operatorId && $this->landingId && $preResult = $this->getByRowAttributes(true, true, true)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по юзеру лендингу и стране
    if ($this->operatorId && $this->landingId && $preResult = $this->getByRowAttributes(true, false, true, true)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по юзеру, провайдеру, оператору
    if ($this->operatorId && $this->landingId && $preResult = $this->getByRowAttributes(true, true, false, false, true)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по юзеру, провайдеру и стране
    if ($this->operatorId && $this->landingId && $preResult = $this->getByRowAttributes(true, false, false, true, true)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по юзеру, лендингу
    if ($this->landingId && $preResult = $this->getByRowAttributes(true, false, true)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по юзеру, провайдеру
    if ($this->landingId && $preResult = $this->getByRowAttributes(true, false, false, false, true)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по юзеру, оператору
    if ($this->operatorId && $preResult = $this->getByRowAttributes(true, true, false)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по юзеру и стране
    if ($this->operatorId && $preResult = $this->getByRowAttributes(true, false, false, true)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по юзеру
    if ($preResult = $this->getByRowAttributes(true, false, false)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по лендингу, оператору
    if ($this->canUseNotPersonalPercent && $this->operatorId && $this->landingId && $preResult = $this->getByRowAttributes(false, true, true)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по лендингу, стране
    if ($this->canUseNotPersonalPercent && $this->operatorId && $this->landingId && $preResult = $this->getByRowAttributes(false, false, true, true)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по провайдеру, оператору
    if ($this->canUseNotPersonalPercent && $this->operatorId && $this->landingId && $preResult = $this->getByRowAttributes(false, true, false, false, true)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по провайдеру, стране
    if ($this->canUseNotPersonalPercent && $this->operatorId && $this->landingId && $preResult = $this->getByRowAttributes(false, false, false, true, true)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по лендингу
    if ($this->canUseNotPersonalPercent && $this->landingId && $preResult = $this->getByRowAttributes(false, false, true)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по провайдеру
    if ($this->canUseNotPersonalPercent && $this->landingId && $preResult = $this->getByRowAttributes(false, false, false, false, true)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по оператору
    if ($this->canUseNotPersonalPercent && $this->operatorId && $preResult = $this->getByRowAttributes(false, true, false)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // по стране
    if ($this->canUseNotPersonalPercent && $this->operatorId && $preResult = $this->getByRowAttributes(false, false, false, true)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // глобальное значение
    if ($this->canUseNotPersonalPercent && $preResult = $this->getByRowAttributes(false, false, false)) {
      $profitValues->loadValuesIfEmpty($preResult);
      if ($profitValues->isFilled()) return $this->formatProfitValues($profitValues);
    }

    // Берём из конфига модуля по типу пользователя
    return $this->getByUserType($profitValues);

  }


  /**
   * @param $isByUser
   * @param $isByOperator
   * @param $isByLanding
   * @param bool $isByCountry
   * @param bool $isByProvider
   * @return PersonalProfit|null
   */
  protected function getByRowAttributes($isByUser, $isByOperator, $isByLanding, $isByCountry = false, $isByProvider = false)
  {
    $models = $isByUser
      ? PersonalProfit::getModelsByUser($this->userId)
      : PersonalProfit::getModelsEmptyUser()
    ;

    // Составляем критерии для поиска
    $criteria = [
      'user_id' => $isByUser ? $this->userId : null,
      'operator_id' => $isByOperator || $isByCountry ? $this->operatorId : null,
      'landing_id' => $isByLanding || $isByProvider ? $this->landingId : null,
      // если по стране и есть оператор, чтобы её найти
      'country_id' => $isByCountry && $this->operatorId ? $this->getCountryId() : null,
      // если по провайдеру и есть лендинг, чтобы его найти
      'provider_id' => $isByProvider && $this->landingId ? $this->getProviderId() : null,
    ];

    // Перебираем все модели, ищем которая удовлетворит всем критериям
    foreach ($models as $model) {
      if ($model->user_id != $criteria['user_id']) {
        continue;
      }

      // если по стране и условие подходит, то по оператору можно не проверять
      if ($model->country_id || $criteria['country_id']) {
        if ($model->operator_id && $model->operator_id != $criteria['operator_id']) {
          // если в условии есть оператор и страна
          continue;
        }
        if (!$model->operator_id && $model->country_id != $criteria['country_id']) {
          continue;
        }
      } elseif ($model->operator_id != $criteria['operator_id']) {
        continue;
      }

      // если по провайдеру и условие подходит, то по лендинг можно не проверять
      if ($model->provider_id || $criteria['provider_id']) {
        if ($model->landing_id && $model->landing_id != $criteria['landing_id']) {
          // если в условии есть лендинг и провайдер
          continue;
        }
        if (!$model->landing_id && $model->provider_id != $criteria['provider_id']) {
          // если в условии есть только провайдер
          continue;
        }
      } elseif ($model->landing_id != $criteria['landing_id']) {
        continue;
      }

      return $model;
    }

    return null;
  }

  /**
   * @return int
   */
  protected function getResellerId()
  {

    if ($reseller = $this->userModule->api('usersByRoles', ['reseller'])->getResult()) {
      return current($reseller)['id'];
    }
    return false;
  }

  /**
   * @param PersonalProfitValues $profitValues
   * @return array
   */
  protected function formatProfitValues(PersonalProfitValues $profitValues)
  {
    return [
      self::REBILL_PARAM => (float)$profitValues->rebillPercent,
      self::BUYOUT_PARAM => (float)$profitValues->buyoutPercent,
      self::CPA_PROFIT_PARAM_RUB => (float)$profitValues->fixedCpaProfitRub,
      self::CPA_PROFIT_PARAM_EUR => (float)$profitValues->fixedCpaProfitEur,
      self::CPA_PROFIT_PARAM_USD => (float)$profitValues->fixedCpaProfitUsd,
      self::UPDATED_AT_PARAM => (int)$profitValues->updatedAt
    ];
  }

  /**
   * @param PersonalProfitValues $values
   * @return array
   */
  protected function getByUserType(PersonalProfitValues $values)
  {

    $module = $this->module;

    switch ($this->userType) {
      case self::USER_TYPE_RESELLER:
        $moduleValues = [
          self::REBILL_PARAM => self::RESELLER_REBILL_PERCENT,
          self::BUYOUT_PARAM => self::RESELLER_BUYOUT_PERCENT,
          self::CPA_PROFIT_PARAM_RUB => 0,
          self::CPA_PROFIT_PARAM_EUR => 0,
          self::CPA_PROFIT_PARAM_USD => 0,
        ];
        break;
      case self::USER_TYPE_PARTNER:
        $moduleValues = [
          self::REBILL_PARAM => (float)$module->settings->getValueByKey($module::SETTINGS_MAIN_REBILL_PERCENT_FOR_PARTNER),
          self::BUYOUT_PARAM => (float)$module->settings->getValueByKey($module::SETTINGS_MAIN_BUYOUT_PERCENT_FOR_PARTNER),
          self::CPA_PROFIT_PARAM_RUB => 0,
          self::CPA_PROFIT_PARAM_EUR => 0,
          self::CPA_PROFIT_PARAM_USD => 0,
        ];
        break;
      default:
        $moduleValues = [
          self::REBILL_PARAM => 100,
          self::BUYOUT_PARAM => 100,
          self::CPA_PROFIT_PARAM_RUB => 0,
          self::CPA_PROFIT_PARAM_EUR => 0,
          self::CPA_PROFIT_PARAM_USD => 0,
        ];
    }

    $values->loadValuesIfEmpty($moduleValues);

    return [
      self::REBILL_PARAM => $values->rebillPercent,
      self::BUYOUT_PARAM => $values->buyoutPercent,
      self::CPA_PROFIT_PARAM_RUB => $values->fixedCpaProfitRub,
      self::CPA_PROFIT_PARAM_EUR => $values->fixedCpaProfitEur,
      self::CPA_PROFIT_PARAM_USD => $values->fixedCpaProfitUsd,
      self::UPDATED_AT_PARAM => $values->updatedAt
    ];
  }

  /**
   * @return int
   */
  protected function getCountryId()
  {
    if (!$this->countryId) {

      $operator = isset(static::$operatorModelCache[$this->operatorId])
        ? static::$operatorModelCache[$this->operatorId]
        : false;

      if (!$operator) {
        $operator = static::$operatorModelCache[$this->operatorId] = Operator::findOne($this->operatorId);
      }

      $operator && $this->countryId = $operator->country_id;
    }

    return $this->countryId;
  }

  /**
   * @return int
   */
  protected function getProviderId()
  {
    if (!$this->providerId) {
      $landing = Landing::findOne($this->landingId);
      $landing && $this->providerId = $landing->provider_id;
    }

    return $this->providerId;
  }
}


