<?php

namespace mcms\statistic\components\newStat\subid;

use mcms\statistic\components\newStat\Group;
use mcms\statistic\components\newStat\subid\query\AliveCB;
use mcms\statistic\components\newStat\subid\query\AliveOns;
use mcms\statistic\components\newStat\subid\query\Ltv;
use mcms\statistic\components\newStat\subid\query\CB;
use mcms\statistic\components\newStat\subid\query\StatisticUser;
use Yii;
use yii\base\NotSupportedException;
use yii\helpers\Inflector;
use yii\web\ForbiddenHttpException;

/**
 * Реализация для нашего текущего получения инфы по subid
 */
class Fetch extends \mcms\statistic\components\newStat\mysql\Fetch
{
  public $rowClass = Row::class;

  /**
   * @inheritdoc
   */
  protected function getQueryClasses()
  {
    return [
      CB::class,
      StatisticUser::class,
      AliveCB::class,
      Ltv::class,
      AliveOns::class,
    ];
  }

  /**
   * фильтруем/группируем запросы
   *
   * @throws ForbiddenHttpException
   * @throws NotSupportedException
   */
  protected function handleQueries()
  {
    $this->queries = array_map(function ($queryClass) {
      return new $queryClass($this->getFormModel());
    }, $this->getQueryClasses());

    $availableGroups = Group::getAvailableGroups();

    foreach ($this->queries as $queryIndex => $query) {
      foreach ($this->getFormModel()->groups as $group) {
        if (!in_array($group, $availableGroups, true)) {
          throw new ForbiddenHttpException('Group is unavailable: ' . $group);
        }

        $methodName = 'handleGroupBy' . Inflector::camelize($group);
        if (!method_exists($query, $methodName)) {
          Yii::error("Ignoring query because method $methodName does not exists in class=" . $query::class);
          unset($this->queries[$queryIndex]);
          continue 2;
        }
        $query->$methodName();
      }

      $query->handleFilterByDates($this->getFormModel()->dateFrom, $this->getFormModel()->dateTo, $this->getFormModel()->ltvDateTo);

      $query->handleFilterByHour($this->getFormModel()->hour);

      if ($this->getFormModel()->getPermissionsChecker()->canFilterByLandingPayTypes()) {
        $query->handleFilterByLandingPayTypes($this->getFormModel()->landingPayTypes);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByProviders()) {
        $query->handleFilterByProviders($this->getFormModel()->providers);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByStreams()) {
        $query->handleFilterByStreams($this->getFormModel()->streams);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterBySources()) {
        $query->handleFilterBySources($this->getFormModel()->sources);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByLandings()) {
        $query->handleFilterByLandings($this->getFormModel()->landings);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByLandingCategories()) {
        $query->handleFilterByLandingCategories($this->getFormModel()->landingCategories);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByOfferCategories()) {
        $query->handleFilterByOfferCategories($this->getFormModel()->offerCategories);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByPlatform()) {
        $query->handleFilterByPlatforms($this->getFormModel()->platforms);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterBySubid1()) {
        $query->handleFilterBySubid1($this->getFormModel()->subid1);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterBySubid2()) {
        $query->handleFilterBySubid2($this->getFormModel()->subid2);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByFakeRevshare()) {
        $query->handleFilterByFake($this->getFormModel()->isFake);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByCountries()) {
        $query->handleFilterByCountries($this->getFormModel()->countries);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByOperators()) {
        $query->handleFilterByOperators($this->getFormModel()->operators);
      }
    }
  }

}
