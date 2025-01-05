<?php

namespace mcms\statistic\components\mainStat\mysql\query;

use mcms\statistic\components\mainStat\FormModel;
use yii\db\Query;

/**
 * Базовый класс для запросов в БД
 */
abstract class BaseQuery extends Query
{
  /**
   * обработчик группировки по датам
   */
  abstract public function handleGroupByDates();
  /**
   * обработчик группировки по месяцам
   */
  abstract public function handleGroupByMonthNumbers();
  /**
   * обработчик группировки по неделям
   */
  abstract public function handleGroupByWeekNumbers();
  /**
   * обработчик группировки по часам
   */
  abstract public function handleGroupByHours();
  /**
   * обработчик группировки по лендам
   */
  abstract public function handleGroupByLandings();
  /**
   * обработчик группировки по ист вебмастера
   */
  abstract public function handleGroupByWebmasterSources();
  /**
   * обработчик группировки по ссылкам
   */
  abstract public function handleGroupByArbitraryLinks();
  /**
   * обработчик группировки по потокам
   */
  abstract public function handleGroupByStreams();
  /**
   * обработчик группировки по платформам
   */
  abstract public function handleGroupByPlatforms();
  /**
   * обработчик группировки по операторам
   */
  abstract public function handleGroupByOperators();
  /**
   * обработчик группировки по странам
   */
  abstract public function handleGroupByCountries();
  /**
   * обработчик группировки по провайдерам
   */
  abstract public function handleGroupByProviders();
  /**
   * обработчик группировки по пользователям
   */
  abstract public function handleGroupByUsers();
  /**
   * обработчик группировки по типам полаты на лендах
   */
  abstract public function handleGroupByLandingPayTypes();
  /**
   * обработчик группировки по менеджерам
   */
  abstract public function handleGroupByManagers();

  /**
   * обработчик фильтрации по датам
   * @param string $dateFrom
   * @param string $dateTo
   */
  abstract public function handleFilterByDates($dateFrom, $dateTo);

  /**
   * обработчик фильтрации по типам полаты на лендах
   * @param int|int[] $types
   */
  abstract public function handleFilterByLandingPayTypes($types);

  /**
   * обработчик фильтрации по провайдерам
   * @param int|int[] $providers
   */
  abstract public function handleFilterByProviders($providers);

  /**
   * обработчик фильтрации по пользователям
   * @param int|int[] $users
   */
  abstract public function handleFilterByUsers($users);

  /**
   * обработчик фильтрации по потокам
   * @param int|int[] $streams
   */
  abstract public function handleFilterByStreams($streams);

  /**
   * обработчик фильтрации по источникам
   * @param int|int[] $sources
   */
  abstract public function handleFilterBySources($sources);

  /**
   * обработчик фильтрации по лендам
   * @param int|int[] $landings
   */
  abstract public function handleFilterByLandings($landings);

  /**
   * обработчик фильтрации по категориям лендов
   * @param int|int[] $landingCategories
   */
  abstract public function handleFilterByLandingCategories($landingCategories);

  /**
   * обработчик фильтрации по платформам
   * @param int|int[] $platforms
   */
  abstract public function handleFilterByPlatforms($platforms);

  /**
   * обработчик фильтрации по фейк/ревшар
   * @param int|int[] $fake
   */
  abstract public function handleFilterByFake($fake);

  /**
   * обработчик фильтрации по валюте
   * @param string $currency
   */
  abstract public function handleFilterByCurrency($currency);

  /**
   * обработчик фильтрации по странам
   * @param int|int[] $countries
   */
  abstract public function handleFilterByCountries($countries);

  /**
   * обработчик фильтрации по операторам
   * @param int|int[] $operators
   */
  abstract public function handleFilterByOperators($operators);

  /**
   * обработчик фильтрации по ревшар/цпа
   * @param string $revshareOrCpa
   */
  abstract public function handleFilterByRevshareOrCpa($revshareOrCpa);

  /**
   * Какие-то кастомные обработчики.
   * Например фильтруем по недоступным юзерам
   * @param FormModel $formModel
   */
  abstract public function handleInitial(FormModel $formModel);
}
