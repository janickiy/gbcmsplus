<?php

namespace mcms\statistic\components\mainStat;

use mcms\statistic\components\CheckPermissions;
use mcms\statistic\components\mainStat\mysql\BaseGroupValuesFormatter;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * Способы группировки статы. В виде объекта вставляется в @see Row::getGroups()
 * НЕ ПУТАТЬ С ГРУППИРОВКОЙ СТОЛБЦОВ!
 */
class Group
{
  const BY_DATES = 'dates';
  const BY_MONTH_NUMBERS = 'monthNumbers';
  const BY_WEEK_NUMBERS = 'weekNumbers';
  const BY_HOURS = 'hours';
  const BY_LANDINGS = 'landings';
  const BY_WEBMASTER_SOURCES = 'webmasterSources';
  const BY_LINKS = 'arbitraryLinks';
  const BY_STREAMS = 'streams';
  const BY_PLATFORMS = 'platforms';
  const BY_OPERATORS = 'operators';
  const BY_COUNTRIES = 'countries';
  const BY_PROVIDERS = 'providers';
  const BY_USERS = 'users';
  const BY_LANDING_PAY_TYPES = 'landingPayTypes';
  const BY_MANAGERS = 'managers';

  const TRANSLATE_GROUP_BY_PREFIX = 'statistic.main_statistic_refactored.group-by-';
  const TRANSLATE_GROUP_PREFIX = 'statistic.main_statistic_refactored.group-';

  /**
   * @var string Тип группировки (одна из констант)
   */
  protected $type;
  /**
   * @var mixed значение. Например айди юзера
   */
  protected $value;

  /** @var BaseGroupValuesFormatter */
  protected $groupFormatter;

  /**
   * Group constructor.
   * @param $type
   * @param $value
   * @param FormModel $formModel
   * @throws \yii\base\InvalidConfigException
   */
  public function __construct($type, $value, FormModel $formModel)
  {
    $this->type = $type;
    $this->value = $value;
    // TODO тут плохо что реализация из папки мускуль подставляется напрямую.
    // Если понадобится расширить, то надо будет сделать фабрику через DI.
    $groupFormatter = Yii::createObject(
      'mcms\statistic\components\mainStat\mysql\groupFormats\\' . Inflector::camelize($type),
      [$value, $formModel]
    );
    if (!$groupFormatter instanceof BaseGroupValuesFormatter) {
      throw new InvalidParamException('There is no such group formatter for type=' . $type);
    }
    $this->groupFormatter = $groupFormatter;
  }

  /**
   * просто геттер значения
   * @return mixed
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * Конфиг группировок
   * @return array
   */
  public static function getGroupsConfig()
  {
    return [
      self::BY_HOURS => [],
      self::BY_DATES => [],
      self::BY_MONTH_NUMBERS => [],
      self::BY_WEEK_NUMBERS => [],
      self::BY_LANDINGS => ['permissionCheckMethod' => 'canGroupByLandings'],
      self::BY_WEBMASTER_SOURCES => ['permissionCheckMethod' => 'canGroupBySources'],
      self::BY_LINKS => ['permissionCheckMethod' => 'canGroupBySources'],
      self::BY_STREAMS => ['permissionCheckMethod' => 'canGroupByStreams'],
      self::BY_PLATFORMS => ['permissionCheckMethod' => 'canGroupByPlatforms'],
      self::BY_OPERATORS => ['permissionCheckMethod' => 'canGroupByOperators'],
      self::BY_COUNTRIES => ['permissionCheckMethod' => 'canGroupByCountries'],
      self::BY_PROVIDERS => ['permissionCheckMethod' => 'canGroupByProviders'],
      self::BY_USERS => ['permissionCheckMethod' => 'canGroupByUsers'],
      self::BY_LANDING_PAY_TYPES => ['permissionCheckMethod' => 'canGroupByLandingPayTypes'],
      self::BY_MANAGERS => ['permissionCheckMethod' => 'canGroupByManagers'],
    ];
  }

  /**
   * Названия группировок в виде: "По потокам", "По датам" и т.д.
   * @return array
   */
  public static function getGroupByLabels()
  {
    $groupKeys = array_keys(static::getGroupsConfig());
    return array_map(
      function ($key) {
        return Yii::_t(self::TRANSLATE_GROUP_BY_PREFIX . $key);
      },
      array_combine($groupKeys, $groupKeys)
    );
  }


  /**
   * Названия группировок в виде: "По потокам", "По датам" и т.д.
   * ТОЛЬКО ДОСТУПНЫЕ ЮЗЕРУ
   * @return array
   */
  public static function getGroupByLabelsAvailable()
  {
    $groupLabels = static::getGroupByLabels();
    $availableKeys = static::getAvailableGroups();

    foreach (array_keys($groupLabels) as $key) {
      if (!in_array($key, $availableKeys, true)) {
        unset($groupLabels[$key]);
      }
    }

    return $groupLabels;
  }

  /**
   * Название колонки для группировки
   * @param $key
   * @return string
   */
  public static function getGroupColumnLabel($key)
  {
    return Yii::_t(self::TRANSLATE_GROUP_PREFIX . $key);
  }

  /**
   * Получить доступные юзеру группировки
   * @return array
   */
  public static function getAvailableGroups()
  {
    /** @var CheckPermissions $checker */
    $checker = Yii::createObject([
      'class' => CheckPermissions::class,
      'viewerId' => Yii::$app->user->id
    ]);

    return array_keys(array_filter(static::getGroupsConfig(), function ($groupConfig) use ($checker) {
      $permissionMethod = ArrayHelper::getValue($groupConfig, 'permissionCheckMethod');
      if (!$permissionMethod) {
        return true;
      }
      return $checker->$permissionMethod();
    }));
  }

  /**
   * Получение отформаттированной сроки для текущего значения и типа. Например если Тип=юзер и Значение=3,
   * то вернет типа такого: '#3 SuperUser'
   * @return string
   */
  public function getFormattedValue()
  {
    return $this->groupFormatter->getFormattedValue();
  }
}
