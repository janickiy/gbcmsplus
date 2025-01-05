<?php

namespace mcms\statistic\components;

use Yii;
use yii\base\Object;

/**
 * Компонент для проверки прав в новой статистике
 */
class CheckPermissions extends Object
{

  public $viewerId;

  /** @var array Закешированные разрешения в виде [userId1 => [permission1 => false, permission2 => true, … ], userId2 => …]*/
  private static $checkAccess = [];

  /**
   * Просмотр доплнительной статистики
   * @return bool
   */
  public function canViewAdditionalStatistic()
  {
    return $this->checkPermission('StatisticViewAdditionalStatistic');
  }

  /**
   * Есть ли право у юзера, который просмотривает стату видеть столбцы по продажам ТБ
   * @return bool
   */
  public function canViewSellTb()
  {
    return $this->checkPermission('StatisticViewSoldTb');
  }

  /**
   * Просмотр профита партнера
   * @return bool
   */
  public function canViewPartnerProfit()
  {
    return $this->checkPermission('StatisticViewPartnerProfit');
  }

  /**
   * Просмотр профита реселлера
   * @return bool
   */
  public function canViewResellerProfit()
  {
    return $this->checkPermission('StatisticViewResellerProfit');
  }

  /**
   * Просмотр админского профита
   * @return bool
   */
  function canViewAdminProfit()
  {
    return $this->checkPermission('StatisticViewAdminProfit');
  }

  /**
   * Просмотр статистики по жалобам
   * @return bool
   */
  public function canViewComplainsStatistic()
  {
    return $this->checkPermission('StatisticViewComplains');
  }

  /**
   * Разрешение смотреть фильтр по меодам оплаты
   * @return bool
   */
  public function canFilterByLandingPayTypes()
  {
    return $this->checkPermission('StatisticFilterByLandingPayTypes');
  }

  /**
   * Разрешение смотреть фильтр по провайдерам
   * @return bool
   */
  public function canFilterByProviders()
  {
    return $this->checkPermission('StatisticFilterByProviders');
  }

  /**
   * Разрешение смотреть скрытые ИК
   * @return bool
   */
  public function canViewHiddenOnetimeSubscriptions()
  {
    return $this->checkPermission('StatisticViewHiddenOnetimeSubscriptions');
  }

  /**
   * Разрешение смотреть фильтр по странам
   * @return bool
   */
  public function canFilterByCountries()
  {
    return $this->checkPermission('StatisticFilterByCountries');
  }

  /**
   * Разрешение смотреть фильтр по операторам
   * @return bool
   */
  public function canFilterByOperators()
  {
    return $this->checkPermission('StatisticFilterByOperators');
  }

  /**
   * Разрешение смотреть фильтр по партнерам
   * @return bool
   */
  public function canFilterByUsers()
  {
    return $this->checkPermission('StatisticFilterByUsers');
  }

  /**
   * Разрешение смотреть фильтр по потокам
   * @return bool
   */
  public function canFilterByStreams()
  {
    return $this->checkPermission('StatisticFilterByStreams');
  }

  /**
   * Разрешение смотреть фильтр по источникам
   * @return bool
   */
  public function canFilterBySources()
  {
    return $this->checkPermission('StatisticFilterBySources');
  }

  /**
   * Разрешение смотреть фильтр по лендингам
   * @return bool
   */
  public function canFilterByLandings()
  {
    return $this->checkPermission('StatisticFilterByLandings');
  }

  /**
   * Разрешение смотреть фильтр по категориям лендингов
   * @return bool
   */
  public function canFilterByLandingCategories()
  {
    return $this->checkPermission('StatisticFilterByLandingCategories');
  }

  /**
   * Разрешение смотреть фильтр по категориям офферов
   * @return bool
   */
  public function canFilterByOfferCategories()
  {
    return $this->checkPermission('StatisticFilterByOfferCategories');
  }

  /**
   * Разрешение смотреть фильтр по платформам
   * @return bool
   */
  public function canFilterByPlatform()
  {
    return $this->checkPermission('StatisticFilterByPlatforms');
  }

  /**
   * Разрешение смотреть фильтр по фейкам
   * @return bool
   */
  public function canFilterByFakeRevshare()
  {
    return $this->checkPermission('StatisticFilterByFakeRevshare');
  }

  /**
   * Разрешение смотреть фильтр по валютам
   * @return bool
   */
  public function canFilterByCurrency()
  {
    return $this->checkPermission('StatisticFilterByCurrency');
  }

  /**
   * Разрешение на фильтрацию по ревшар/цпа
   * @return bool
   */
  public function canFilterByRevshareOrCpa()
  {
    // пока просто true, т.к. нет такого пермишена
    return true;
  }

  /**
   * @return bool
   */
  public function canFilterBySubid1()
  {
    // пока просто true, т.к. нет такого пермишена
    return true;
  }

  /**
   * @return bool
   */
  public function canFilterBySubid2()
  {
    // пока просто true, т.к. нет такого пермишена
    return true;
  }

  /**
   * Разрешение группировать по часам
   * @return bool
   */
  public function canGroupByHours()
  {
    return $this->checkPermission('StatisticGroupByHours');
  }


  public function canGroupByLandings()
  {
    return $this->checkPermission('StatisticGroupByLandings');
  }

  public function canGroupBySources()
  {
    return $this->checkPermission('StatisticGroupBySources');
  }

  public function canGroupByStreams()
  {
    return $this->checkPermission('StatisticGroupByStreams');
  }

  public function canGroupByPlatforms()
  {
    return $this->checkPermission('StatisticGroupByPlatforms');
  }

  public function canGroupByOperators()
  {
    return $this->checkPermission('StatisticGroupByOperators');
  }

  public function canGroupByCountries()
  {
    return $this->checkPermission('StatisticGroupByCountries');
  }

  public function canGroupByProviders()
  {
    return $this->checkPermission('StatisticGroupByProviders');
  }

  public function canGroupByUsers()
  {
    return $this->checkPermission('StatisticGroupByUsers');
  }

  public function canGroupByLandingPayTypes()
  {
    return $this->checkPermission('StatisticGroupByLandingPayTypes');
  }

  public function canGroupByManagers()
  {
    return $this->checkPermission('StatisticGroupByManagers');
  }

  /**
   * проверяем право и кладем в $_checkAccess для кэширования
   * @param $permission
   * @return bool
   */
  protected function checkPermission($permission)
  {
    $userAccess = isset(self::$checkAccess[$this->viewerId])
      ? self::$checkAccess[$this->viewerId]
      : []
    ;

    if (isset($userAccess[$permission])) { // есть в кэше
      return $userAccess[$permission];
    }

    // нет в кэше, достаем
    $hasPermission = Yii::$app->authManager->checkAccess($this->viewerId, $permission);

    self::$checkAccess[$this->viewerId][$permission] = $hasPermission;
    return $hasPermission;
  }
}
