<?php

namespace mcms\promo\components;

use mcms\promo\models\Provider;

/**
 * Interface ProviderSyncInterface
 * @package mcms\promo
 */
interface ProviderSyncInterface
{
  /**
   * авторизация на провайдере
   * @return bool
   */
  public function auth();

  /**
   * синкаем страны
   * @param $checkUpdateTime
   * @param $deleteInsteadDeactivate
   */
  public function syncCountries($checkUpdateTime, $deleteInsteadDeactivate);

  /**
   * синкаем операторов
   * @param $checkUpdateTime
   * @param $deleteInsteadDeactivate
   */
  public function syncOperators($checkUpdateTime, $deleteInsteadDeactivate);

  /**
   * синкаем лендосы
   * @param $checkUpdateTime
   * @return int[]
   */
  public function syncLandings($checkUpdateTime);

  /**
   * синкаем рейтинги лендосы
   * @return int[]
   */
  public function syncRating();

  /**
   * синкаем внешние провайдеров
   */
  public function syncExternalProviders();

  /**
   * синкаем лимиты подписок
   */
  public function syncCap();

  /**
   * синкаем сервисы
   */
  public function syncServices();

  /**
   * Логгируем ошибку и отображаем
   * @param $text
   */
  public function error($text);

  /**
   * Логгируем варнинг и отображаем
   * @param $text
   */
  public function warning($text);

  /**
   * Получение сырых данных из запроса стран
   * @return string
   */
  public function getCountriesFromApi();
  /**
   * Получение сырых данных из запроса операторов
   * @return string
   */
  public function getOperatorsFromApi();
  /**
   * Получение сырых данных из запроса лендингов
   * @return string
   */
  public function getLandingsFromApi();
  /**
   * Получение внешних провайдеров
   * @return string
   */
  public function getExternalProvidersFromApi();
  /**
   * Получение лимита подписок
   * @return string
   */
  public function getCapFromApi();
  /**
   * Получение сервисов
   * @return string
   */
  public function getServicesFromApi();
}
