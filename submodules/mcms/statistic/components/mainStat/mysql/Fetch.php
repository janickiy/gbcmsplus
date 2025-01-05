<?php

namespace mcms\statistic\components\mainStat\mysql;

use mcms\statistic\components\mainStat\BaseFetch;
use mcms\statistic\components\mainStat\DataProvider;
use mcms\statistic\components\mainStat\DataProviderInterface;
use mcms\statistic\components\mainStat\Group;
use mcms\statistic\components\mainStat\mysql\query\BaseQuery;
use mcms\statistic\components\mainStat\mysql\query\Complains;
use mcms\statistic\components\mainStat\mysql\query\Hits;
use mcms\statistic\components\mainStat\mysql\query\Onetime;
use mcms\statistic\components\mainStat\mysql\query\SellTbHits;
use mcms\statistic\components\mainStat\mysql\query\SoldTb;
use mcms\statistic\components\mainStat\mysql\query\Subscriptions;
use mcms\statistic\components\mainStat\mysql\query\SoldSubscriptions;
use Yii;
use yii\helpers\BaseInflector;
use yii\helpers\Inflector;
use yii\web\ForbiddenHttpException;

/**
 * Реализация для нашего текущего получения инфы из мускуля.
 * Если хотим доставать инфу в стату по-другому, то можно реализовать похожий класс унаследованный от BaseFetch.
 */
class Fetch extends BaseFetch
{

  public $rowClass = Row::class;

  /**
   * Запросы, которые участвуют в извлечении данных из БД
   * @var BaseQuery[]
   */
  protected $queries;

  /**
   * @inheritdoc
   */
  public function getDataProvider($config = ['pagination' => ['pageSize' => 1000]])
  {
    $dataProvider = new DataProvider($config);

    $dataProvider->footerRow = Yii::createObject([
      'class' => $this->rowClass,
      'currency' => $this->getFormModel()->currency
    ]);

    if (!$this->getFormModel()->validate()) {
      return $dataProvider;
    }

    $this->queries = array_map(function ($queryClass) {
      return Yii::createObject($queryClass);
    }, $this->getQueryClasses());

    // фильтруем/группируем запросы
    $this->handleQueries();

    // создаем строки для провайдера
    $dataProvider->allModels = $this->makeRows();

    // расчитываем данные для строки футера
    $this->populateFooterRow($dataProvider);

    return $dataProvider;
  }

  /**
   * Строка футера. Для неё суммируем все исходные данные.
   * Остальные данные будут вычислены по той же логике что и обычная строка, т.к. это тот же объект Row
   * @param DataProviderInterface $dataProvider
   */
  protected function populateFooterRow(DataProviderInterface $dataProvider)
  {
    // все исходные данные берём из публичных свойств класса
    $vars = array_keys(get_class_vars(get_class($dataProvider->footerRow->rowDataDto)));
    $i = 0;
    // Считаем сумму только тех строк, которые показываем
    $limit = $dataProvider->getPagination()->getLimit();
    foreach ($dataProvider->getModels() as $row) {
      $i++;
      if ($limit && $i > $limit) {
        break;
      }
      foreach ($vars as $var) {
        $dataProvider->footerRow->rowDataDto->{$var} += $row->rowDataDto->{$var};
      }
    }
  }

  /**
   * Список класснеймов для получения инфы
   * @return array
   */
  protected function getQueryClasses()
  {
    return [
      Hits::class,
      Subscriptions::class,
      SoldSubscriptions::class,
      Onetime::class,
      SoldTb::class,
      SellTbHits::class,
      Complains::class
    ];
  }

  /**
   * фильтруем/группируем запросы из $this->queries
   * @throws ForbiddenHttpException
   */
  protected function handleQueries()
  {
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

      $query->handleInitial($this->getFormModel());

      $query->handleFilterByDates($this->getFormModel()->dateFrom, $this->getFormModel()->dateTo);

      if ($this->getFormModel()->getPermissionsChecker()->canFilterByLandingPayTypes()) {
        $query->handleFilterByLandingPayTypes($this->getFormModel()->landingPayTypes);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByProviders()) {
        $query->handleFilterByProviders($this->getFormModel()->providers);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByUsers()) {
        $query->handleFilterByUsers($this->getFormModel()->users);
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
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByPlatform()) {
        $query->handleFilterByPlatforms($this->getFormModel()->platforms);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByFakeRevshare()) {
        $query->handleFilterByFake($this->getFormModel()->isFake);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByCurrency()) {
        $query->handleFilterByCurrency($this->getFormModel()->currency);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByCountries()) {
        $query->handleFilterByCountries($this->getFormModel()->countries);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByOperators()) {
        $query->handleFilterByOperators($this->getFormModel()->operators);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByRevshareOrCpa()) {
        $query->handleFilterByRevshareOrCpa($this->getFormModel()->revshareOrCpa);
      }
    }
  }

  /**
   * Получаем данные из $this->queries и предствляем в виде экземпляров Row
   * @return Row[]
   */
  protected function makeRows()
  {
    $rowModels = [];

    foreach ($this->queries as $query) {
      foreach ($query->all() as $item) {
        $groups = [];

        foreach ($this->getFormModel()->groups as $group) {
          $groups[$group] = new Group($group, $item[$group], $this->getFormModel());
        }

        $key = implode('-', array_map(function (Group $group) {
          return $group->getValue();
        }, $groups));

        if (!isset($rowModels[$key]) || !($rowModels[$key] instanceof Row)) {
          $rowModels[$key] = Yii::createObject([
            'class' => $this->rowClass,
            'currency' => $this->getFormModel()->currency
          ]);
        }
        $rowModels[$key]->setGroup($key);
        $rowModels[$key]->groups = $groups;

        foreach ($item as $itemKey => $itemValue) {
          $property = lcfirst(BaseInflector::camelize($itemKey));
          if (!property_exists($rowModels[$key]->rowDataDto, $property)) {
            // Если нет такого поля, значит это группировка. Идем дальше
            continue;
          }
          $rowModels[$key]->rowDataDto->$property = $itemValue;
        }
      }
    }

    return $rowModels;
  }
}
