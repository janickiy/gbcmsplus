<?php

namespace mcms\common\module;

use mcms\common\exceptions\api\ApiResultInvalidException;
use mcms\common\exceptions\api\ClassNameNotDefinedException;
use mcms\common\module\api\ApiResult;
use mcms\notifications\components\events\TelegramAutoUnsubscribeEvent;
use mcms\notifications\components\telegram\Api;
use mcms\payments\models\ExchangerCourse;
use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\models\UserPromoSetting;
use rgk\settings\components\SettingsManager;
use Yii;
use yii\base\Event;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

use mcms\promo\components\events\module_settings\PartnerBuyoutPercentChanged;
use mcms\promo\components\events\module_settings\PartnerRebillPercentChanged;

abstract class Module extends \yii\base\Module
{
  /** @var  int */
  public $db_id;

  public $messages;

  /** @var  SettingsManager */
  public $settings;

  public $events;
  public $name;
  public $menu;
  public $preload = false;
  public $is_enabled = false;

  public $apiClasses;

  /** @var array  */
  public $fixtures = [];

  public function init()
  {
    $this->settings = Yii::$app->get('settingsManager');
    parent::init();

    // Проверяем, навешено ли событие обработки изменения настроек
    $hasHandler = Event::hasHandlers(SettingsManager::class, SettingsManager::EVENT_SET_VALUES);
    if (!$hasHandler) {
      Event::on(SettingsManager::class, SettingsManager::EVENT_SET_VALUES, function ($event) {
        $this->afterSettingsSave($event->settingKeys, $event->oldSettings, $event->newSettings);
      });
    }

  }

  /**
   * @param $operationType
   * @param array $params
   * @return ApiResult | FixtureApi
   * @throws ApiResultInvalidException
   * @throws ClassNameNotDefinedException
   */
  public function api($operationType, $params = [])
  {
    if (!$className = ArrayHelper::getValue($this->apiClasses, $operationType, false)) {
      $exception = new ClassNameNotDefinedException;
      $exception->className = $operationType;

      throw $exception;
    }

    $data = new $className($params);

    if (!$data instanceof ApiResult) throw new ApiResultInvalidException;

    return $data;
  }

  /**
   * Событие после сохранения настроек
   * @param $changedSettings
   * @param $oldSettings
   * @param $newSettings
   */
  public function afterSettingsSave($changedSettings, $oldSettings, $newSettings) {
    /** @var \mcms\user\Module $moduleUser */
    $moduleUser = Yii::$app->getModule('users');
    // Module promo
    if (
      in_array(\mcms\notifications\Module::SETTINGS_TELEGRAM_BOT_NAME, $changedSettings) ||
      in_array(\mcms\notifications\Module::SETTINGS_TELEGRAM_BOT_TOKEN, $changedSettings)
    ) {
      // Сетим новый хук
      $url = Url::toRoute(['/notifications/api/telegram/'], 'https');
      Api::setWebhook($url);

      // получаем параметры всех пользователей с заполненым telegram_id
      $userParams = $moduleUser
        ->api('userTelegram')
        ->getUsersWithTelegram();

      foreach ($userParams as $userParam) {
        // очищаем значение telegram_id
        $moduleUser
          ->api('userTelegram', ['userId' => $userParam->user_id])
          ->unsetTelegramId();
        // уведомляем пользователя, что у него очистился telegram_id
        (new TelegramAutoUnsubscribeEvent($userParam->user))->trigger();
      }
    }

    /** чистим кэш микросервиса для настроек */
    foreach ($changedSettings as $changedSetting) {
      ApiHandlersHelper::bufferedClearCache(ApiHandlersHelper::RGK_SETTINGS_CACHE_PREFIX . $changedSetting);
    }
    ApiHandlersHelper::bufferedClearCache(null);

    // Module promo
    // Необходимо сбросить кэш который генерится в api GetPersonalProfit
    if (
      in_array(\mcms\promo\Module::SETTINGS_MAIN_BUYOUT_PERCENT_FOR_PARTNER, $changedSettings) ||
      in_array(\mcms\promo\Module::SETTINGS_MAIN_REBILL_PERCENT_FOR_PARTNER, $changedSettings)
    ) {
      TagDependency::invalidate(Yii::$app->cache, ['personal_profit__module_percents']);
    }

    if (in_array(\mcms\promo\Module::SETTINGS_MAIN_REBILL_PERCENT_FOR_PARTNER, $changedSettings)) {
      (new PartnerRebillPercentChanged($oldSettings, $newSettings))->trigger();
    }

    if (in_array(\mcms\promo\Module::SETTINGS_MAIN_BUYOUT_PERCENT_FOR_PARTNER, $changedSettings)) {
      (new PartnerBuyoutPercentChanged($oldSettings, $newSettings))->trigger();
    }

    if (in_array(\mcms\promo\Module::SETTINGS_FAKE_ADD_AFTER_SUBSCRIPTIONS, $changedSettings)
      || in_array(\mcms\promo\Module::SETTINGS_FAKE_ADD_SUBSCRIPTION_PERCENT, $changedSettings)
      || in_array(\mcms\promo\Module::SETTINGS_FAKE_OFF_SUBSCRIPTION_DAYS, $changedSettings)
      || in_array(\mcms\promo\Module::SETTINGS_FAKE_OFF_SUBSCRIPTION_PERCENT_BEFORE_DAYS, $changedSettings)
      || in_array(\mcms\promo\Module::SETTINGS_GLOBAL_ENABLE_FAKE_TO_USERS, $changedSettings)
    ) {
      ApiHandlersHelper::clearCache('fake-revshare-settings');
    }

    if (in_array(\mcms\promo\Module::SETTINGS_ALLOW_SOURCE_REDIRECT, $changedSettings)) {
      ApiHandlersHelper::cacheFlushAll();
    }

    if (in_array(\mcms\promo\Module::SETTINGS_ENABLE_BUYOUT_FOR_PARTNERS, $changedSettings)) {
      $newValue = (bool)ArrayHelper::getValue(
        ArrayHelper::getValue($newSettings, \mcms\promo\Module::SETTINGS_ENABLE_BUYOUT_FOR_PARTNERS),
        0
      );
      if ($newValue == false) {
        $partners = $moduleUser
          ->api('usersByRoles', ['pagination' => false, $moduleUser::PARTNER_ROLE])
            ->setResultTypeDataProvider()
          ->getResult()
          ->getModels();

        // Если выключен выкуп для партнеров, меняем все их ленды на rebill
        UserPromoSetting::changeSourceOperatorLandingsProfitType($partners);
      }
    }

    $exchangerCourses = [
      \mcms\payments\Module::SETTINGS_EXCHANGE_PERCENT_USD_RUR,
      \mcms\payments\Module::SETTINGS_EXCHANGE_PERCENT_RUR_USD,
      \mcms\payments\Module::SETTINGS_EXCHANGE_PERCENT_USD_EUR,
      \mcms\payments\Module::SETTINGS_EXCHANGE_PERCENT_EUR_USD,
      \mcms\payments\Module::SETTINGS_EXCHANGE_PERCENT_EUR_RUR,
      \mcms\payments\Module::SETTINGS_EXCHANGE_PERCENT_RUR_EUR,
    ];
    if (array_intersect($exchangerCourses, $changedSettings)) {
      /** @var \mcms\payments\components\api\ExchangerCourses $exchangerApi */
      $exchangerApi = Yii::$app->getModule('payments')
        ->api('exchangerCourses', ['useCachedResults' => false]);

      ExchangerCourse::storeCurrencyCourses($exchangerApi->getCurrencyCourses());
      ExchangerCourse::invalidateCache();
    }

    $availableCurrencies = [
      \mcms\promo\Module::SETTING_AVAILABLE_RUB,
      \mcms\promo\Module::SETTING_AVAILABLE_USD,
      \mcms\promo\Module::SETTING_AVAILABLE_EUR,
    ];

    if (array_intersect($availableCurrencies, $changedSettings)) {
      Yii::$app->getModule('promo')->api('mainCurrencies')->invalidateCache();
    }

  }

}
