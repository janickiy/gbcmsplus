<?php

namespace mcms\statistic\components\newStat\mysql;

use mcms\statistic\components\newStat\mysql\query\Alive;
use mcms\statistic\components\newStat\mysql\query\Alive30;
use mcms\statistic\components\newStat\mysql\query\AliveSearch;
use mcms\statistic\components\newStat\mysql\query\Buyout;
use mcms\statistic\components\newStat\mysql\query\CorrectedRebills;
use mcms\statistic\components\newStat\mysql\query\CorrectedRebills24;
use mcms\statistic\components\newStat\mysql\query\Ltv;
use mcms\statistic\components\newStat\mysql\query\Onetime;
use mcms\statistic\components\newStat\mysql\query\Complains;
use mcms\statistic\components\newStat\BaseFetch;
use mcms\statistic\components\newStat\DataProvider;
use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\Group;
use mcms\statistic\components\newStat\mysql\query\BaseQuery;
use mcms\statistic\components\newStat\mysql\query\Hits;
use mcms\statistic\components\newStat\mysql\query\Refunds;
use mcms\statistic\components\newStat\mysql\query\Subscriptions;
use RuntimeException;
use Yii;
use yii\data\Sort;
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
   * @var int
   */
  protected $columnsTemplateId;

  /**
   * @var array Значения живых подписок в ИТОГО. В виде ключа выступает название свойства.
   * В виде значения хранится самая свежая дата (или неделя|месяц).
   */
  private $_aliveTotalValues = [
    'revshareTotalOnsWithoutOffs' => null,
    'toBuyoutTotalOnsWithoutOffs' => null,
    'toBuyoutAliveOns' => null,
    'revshareAliveOns' => null,
    'revshareAlive30Ons' => null,
    'toBuyoutAlive30Ons' => null,
  ];

  /**
   * @inheritdoc
   */
  public function __construct(FormModel $formModel, $columnsTemplateId)
  {
    parent::__construct($formModel);
    $this->columnsTemplateId = $columnsTemplateId;
  }

  /**
   * @inheritdoc
   */
  public function getDataProvider($config = ['pagination' => ['pageSize' => 1000]])
  {
    // Пропихиваем в датапровайдер нужный Row
    $config['modelClass'] = $this->rowClass;
    $dataProvider = new DataProvider($config);

    // TRICKY: Если передать массив сортировки вместо объекта, параметр сортировки будет называться не sort
    // а как-то рандомно @see \yii\data\BaseDataProvider::setSort()
    // из-за этого поломалась сортировка во второй группировке
    $sort = new Sort([
      'attributes' =>
        [
          'groups' => $this->getGroupSorting(),
          'hits' => ['default' => SORT_DESC],
          'accepted' => ['default' => SORT_DESC],
          'totalSubscriptions' => ['default' => SORT_DESC],
          'totalSubscriptionsRate' => ['default' => SORT_DESC],
          'totalOffs' => ['default' => SORT_DESC],
          'totalCharges' => ['default' => SORT_DESC],
          'totalChargesNotified' => ['default' => SORT_DESC],
          'totalPartnerProfit' => ['default' => SORT_DESC],
          'totalResellerLtvProfit' => ['default' => SORT_DESC],
          'totalArpu' => ['default' => SORT_DESC],
          'aliveOns' => ['default' => SORT_DESC],
          'totalOnsWithoutOffs' => ['default' => SORT_DESC],
          'unsubscribers' => ['default' => SORT_DESC],
          'chargesTotal' => ['default' => SORT_DESC],
          'totalResellerProfit' => ['default' => SORT_DESC],
          'buyoutResellerNetProfit' => ['default' => SORT_DESC],
          'otpResellerNetProfit' => ['default' => SORT_DESC],
          'totalOtpResellerNetProfit' => ['default' => SORT_DESC],
          'totalResellerNetProfit' => ['default' => SORT_DESC],
          'rgkComplaints' => ['default' => SORT_DESC],
          'callMnoComplaints' => ['default' => SORT_DESC],
          'rgkRefundSum' => ['default' => SORT_DESC],
          'mnoRefundSum' => ['default' => SORT_DESC],
          'refundSum' => ['default' => SORT_DESC],

          'revshareHits' => ['default' => SORT_DESC],
          'revshareAccepted' => ['default' => SORT_DESC],
          'revshareOns' => ['default' => SORT_DESC],
          'revshareCr' => ['default' => SORT_DESC],
          'revshareOffs' => ['default' => SORT_DESC],
          'revshareRebills' => ['default' => SORT_DESC],
          'revshareRebillsNotified' => ['default' => SORT_DESC],
          'revshareRebills24' => ['default' => SORT_DESC],
          'revshareOffs24' => ['default' => SORT_DESC],
          'revshareResellerLtvProfit' => ['default' => SORT_DESC],
          'revshareArpu' => ['default' => SORT_DESC],
          'revshareAliveOns' => ['default' => SORT_DESC],
          'revshareTotalOnsWithoutOffs' => ['default' => SORT_DESC],
          'revshareUnsubscribers' => ['default' => SORT_DESC],
          'revshareRebillsTotal' => ['default' => SORT_DESC],
          'revshareFixComissions' => ['default' => SORT_DESC],
          'revshareAdjustment' => ['default' => SORT_DESC],
          'revshareResellerProfit' => ['default' => SORT_DESC],
          'revsharePartnerProfit' => ['default' => SORT_DESC],
          'totalRevsharePartnerProfit' => ['asc' => ['revsharePartnerProfit' => SORT_ASC], 'desc' => ['revsharePartnerProfit' => SORT_DESC], 'default' => SORT_DESC],
          'revshareResellerNetProfit' => ['default' => SORT_DESC],
          'revshareTotalMargin' => ['default' => SORT_DESC],
          'totalRevshareResellerNetProfit' => ['default' => SORT_DESC],
          'revshareRgkComplaints' => ['default' => SORT_DESC],
          'revshareCallMnoComplaints' => ['default' => SORT_DESC],
          'revshareRgkRefundSum' => ['default' => SORT_DESC],
          'revshareMnoRefundSum' => ['default' => SORT_DESC],
          'revshareRefundSum' => ['default' => SORT_DESC],

          'toBuyoutHits' => ['default' => SORT_DESC],
          'toBuyoutAccepted' => ['default' => SORT_DESC],
          'toBuyoutOns' => ['default' => SORT_DESC],
          'toBuyoutCr' => ['default' => SORT_DESC],
          'toBuyoutOffs' => ['default' => SORT_DESC],
          'buyoutRoi' => ['default' => SORT_DESC],
          'buyoutVisibleOns' => ['default' => SORT_DESC],
          'buyoutAvgPartnerProfit' => ['default' => SORT_DESC],
          'buyoutRebills' => ['default' => SORT_DESC],
          'buyoutRebills24' => ['default' => SORT_DESC],
          'buyoutRpm' => ['default' => SORT_DESC],
          'buyoutNotifyRpm' => ['default' => SORT_DESC],
          'cpaResellerLtvProfit' => ['asc' => ['toBuyoutResellerLtvProfit' => SORT_ASC], 'desc' => ['toBuyoutResellerLtvProfit' => SORT_DESC], 'default' => SORT_DESC],
          'toBuyoutAliveOns' => ['default' => SORT_DESC],
          'toBuyoutTotalOnsWithoutOffs' => ['default' => SORT_DESC],
          'buyoutUnsubscribers' => ['default' => SORT_DESC],
          'buyoutChargesTotal' => ['default' => SORT_DESC],
          'buyoutResellerProfit' => ['default' => SORT_DESC],
          'buyoutPartnerProfit' => ['default' => SORT_DESC],
          'buyoutMargin' => ['default' => SORT_DESC],
          'buyoutOffs24' => ['default' => SORT_DESC],
          'totalBuyoutPartnerProfit' => ['default' => SORT_DESC],
          'toBuyoutArpu' => ['default' => SORT_DESC],
          'toBuyoutRgkComplaints' => ['default' => SORT_DESC],
          'toBuyoutCallMnoComplaints' => ['default' => SORT_DESC],
          'toBuyoutRgkRefundSum' => ['default' => SORT_DESC],
          'toBuyoutMnoRefundSum' => ['default' => SORT_DESC],
          'toBuyoutRefundSum' => ['default' => SORT_DESC],

          'otpHits' => ['default' => SORT_DESC],
          'otpAccepted' => ['default' => SORT_DESC],
          'otpOns' => ['default' => SORT_DESC],
          'otpCr' => ['default' => SORT_DESC],
          'otpPartnerProfit' => ['default' => SORT_DESC],
          'totalOtpPartnerProfit' => ['default' => SORT_DESC],
          'otpAvgPartnerProfit' => ['default' => SORT_DESC],
          'otpRpm' => ['default' => SORT_DESC],
          'otpNotifyRpm' => ['default' => SORT_DESC],
          'otpResellerProfit' => ['default' => SORT_DESC],
          'otpPayoutNotified' => ['default' => SORT_DESC],
          'otpFixCommissions' => ['default' => SORT_DESC],
          'otpAdjustment' => ['default' => SORT_DESC],
          'otpTotalMargin' => ['default' => SORT_DESC],
          'otpRgkComplaints' => ['default' => SORT_DESC],
          'otpCallMnoComplaints' => ['default' => SORT_DESC],
          'otpRgkRefundSum' => ['default' => SORT_DESC],
          'otpMnoRefundSum' => ['default' => SORT_DESC],
          'otpRefundSum' => ['default' => SORT_DESC],
          'group' => ['default' => SORT_DESC],
        ],
      'defaultOrder' => [
        'groups' => $this->getDefaultSortingDirection()
      ],
    ]);

    $dataProvider->setSort($sort);

    $dataProvider->sumRow = Yii::createObject([
      'class' => $this->rowClass,
      'currency' => $this->getFormModel()->currency
    ]);

    $dataProvider->avgRow = Yii::createObject([
      'class' => $this->rowClass,
      'currency' => $this->getFormModel()->currency
    ]);

    if (!$this->getFormModel()->validate()) {
      return $dataProvider;
    }

    // фильтруем/группируем запросы
    $this->handleQueries();

    // создаем строки для провайдера
    try {
      $dataProvider->allModels = $this->getFormModel()->secondGroup ? $this->makeSecondGroupRows() : $this->makeRows();
    } catch (\Exception $e) {
      $dataProvider->allModels = [];
    }

    // totalCount для пагинации
    $dataProvider->getPagination()->totalCount = count($dataProvider->allModels);

    // расчитываем данные для строки футера
    $this->populateFooterRow($dataProvider);

    return $dataProvider;
  }

  /**
   * Строка футера. Для неё суммируем все исходные данные.
   * Остальные данные будут вычислены по той же логике что и обычная строка, т.к. это тот же объект Row
   * @param DataProvider $dataProvider
   */
  protected function populateFooterRow(DataProvider $dataProvider)
  {
    // все исходные данные берём из публичных свойств класса
    $vars = array_keys(get_object_vars((new $dataProvider->modelClass)->getRowDataDto()));
    $i = 0;
    foreach ($dataProvider->allModels as $row) {
      /** @var Row $row */
      $i++;
      foreach ($vars as $var) {
        if (!array_key_exists($var, $this->_aliveTotalValues) || !$this->getFormModel()->hasGroupBy([Group::BY_HOURS, Group::BY_DATES, Group::BY_WEEK_NUMBERS, Group::BY_MONTH_NUMBERS])) {
          $dataProvider->sumRow->rowDataDto->{$var} += $row->rowDataDto->{$var};
          continue;
        }

        // кастомный расчёт Avg для некоторых полей для которых надо брать последнее значение
        $dataProvider->avgRow->rowDataDto->{$var} += $row->rowDataDto->{$var};

        // кастомный расчёт Total для некоторых полей для которых надо брать последнее значение
        $maximumGroupValue = $this->_aliveTotalValues[$var];
        if ($maximumGroupValue >= $row->getGroup()) {
          continue;
        }


        $this->_aliveTotalValues[$var] = $row->getGroup();
        $dataProvider->sumRow->rowDataDto->{$var} = $row->rowDataDto->{$var};
      }
    }

    foreach ($vars as $var) {
      if (array_key_exists($var, $this->_aliveTotalValues) && $this->getFormModel()->hasGroupBy([Group::BY_HOURS, Group::BY_DATES, Group::BY_WEEK_NUMBERS, Group::BY_MONTH_NUMBERS])) {
        $dataProvider->avgRow->rowDataDto->{$var} = $i > 0
          ? $dataProvider->avgRow->rowDataDto->{$var} / $i
          : 0;
        continue;
      }
      $dataProvider->avgRow->rowDataDto->{$var} = $i > 0
        ? $dataProvider->sumRow->rowDataDto->{$var} / $i
        : 0;
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
      Complains::class,
      Subscriptions::class,
      Buyout::class,
      Onetime::class,
      Alive::class,
      AliveSearch::class,
      Alive30::class,
      Ltv::class,
      CorrectedRebills::class,
      CorrectedRebills24::class,
      Refunds::class,
    ];
  }

  /**
   * фильтруем/группируем запросы из $this->queries
   * @throws ForbiddenHttpException
   */
  protected function handleQueries()
  {
    $this->queries = array_map(function ($queryClass) {
      $object = Yii::createObject([
        'class' => $queryClass,
        'templateId' => $this->columnsTemplateId
      ]);

      return $object->isQueryNeeded() ? $object : false;
    }, $this->getQueryClasses());

    $this->queries = array_filter($this->queries);

    $availableGroups = Group::getAvailableGroups();

    foreach ($this->queries as $queryIndex => $query) {
      foreach ($this->getFormModel()->groups as $group) {
        if (!in_array($group, $availableGroups, true)) {
          // TODO: Стата используется в complex filters. Если запретить какую-то группировку, которая используется там, стата упадет
          // TODO: подумать, что с этим делать
          //throw new ForbiddenHttpException('Group is unavailable: ' . $group);
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

      if ($query instanceof Alive && !$this->getFormModel()->hasGroupBy([Group::BY_DATES, Group::BY_WEEK_NUMBERS, Group::BY_MONTH_NUMBERS])) {
        // для живых подписок мы должны брать самые актуальные значения
        // но если группируем не по временным полям, то применяем обычный фильтр
        $query->handleFilterByDates($this->getFormModel()->dateTo, $this->getFormModel()->dateTo, $this->getFormModel()->ltvDateTo);
      } elseif ($query instanceof Alive30 && $this->getFormModel()->hasGroupBy(Group::BY_HOURS)) {
        // для живых подписок мы должны брать самые актуальные значения
        $query->handleFilterByDates($this->getFormModel()->dateTo, $this->getFormModel()->dateTo, $this->getFormModel()->ltvDateTo);
      } else {
        $query->handleFilterByDates($this->getFormModel()->dateFrom, $this->getFormModel()->dateTo, $this->getFormModel()->ltvDateTo);
      }

      $query->handleFilterByHour($this->getFormModel()->hour);

      if ($this->getFormModel()->getPermissionsChecker()->canGroupByManagers()) {
        $query->handleFilterByManagers($this->getFormModel()->manager);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByLandingPayTypes()) {
        $query->handleFilterByLandingPayTypes($this->getFormModel()->landingPayTypes);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByProviders()) {
        $query->handleFilterByProviders($this->getFormModel()->getProviders());
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
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByOfferCategories()) {
        $query->handleFilterByOfferCategories($this->getFormModel()->offerCategories);
      }
      if ($this->getFormModel()->getPermissionsChecker()->canFilterByPlatform()) {
        $query->handleFilterByPlatforms($this->getFormModel()->platforms);
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
          if (!array_key_exists($group, $item)) {
            throw new RuntimeException('запрос ' . $query::class . ' составлен некорректно');
          }

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

  /**
   * Получаем данные из $this->queries и предствляем в виде экземпляров Row для двойной группировки
   * @return Row[]
   */
  protected function makeSecondGroupRows()
  {
    $rowModels = [];

    foreach ($this->queries as $query) {
      foreach ($query->all() as $item) {
        $groups = [];

        foreach ($this->getFormModel()->groups as $group) {
          $groups[$group] = new Group($group, $item[$group], $this->getFormModel());
        }

        //Изменяем группировку и добавляем фильтрацию
        /* @var $secondGroupQuery BaseQuery */
        $secondGroupQuery = new $query;

        $groupByMethodName = 'handleGroupBy' . Inflector::camelize($this->getFormModel()->secondGroup);
        if (!method_exists($secondGroupQuery, $groupByMethodName)) {
          Yii::error("Ignoring query because method $groupByMethodName does not exists in class=" . $query::class);
          continue;
        }
        $secondGroupQuery->$groupByMethodName();

        $filterMethodName = 'handleFilterBy' . Inflector::camelize($group);
        $filterValue = $item[$group];

        $secondGroupQuery->handleInitial($this->getFormModel());

        if ($group == Group::BY_LINKS) {
          $filterMethodName = 'handleFilterBySources';
        }

        if ($group === Group::BY_HOURS) {
          $this->getFormModel()->hour = $filterValue;
          $filterMethodName = 'handleFilterByHour';
        }

        $dateTo = $this->getFormModel()->dateTo;
        $dateFrom = $this->getFormModel()->dateFrom;
        if (in_array($group, [Group::BY_DATES, Group::BY_WEEK_NUMBERS, Group::BY_MONTH_NUMBERS])) {
          $dateTo = $filterValue;
          $dateFrom = $filterValue;
        } else {
          $secondGroupQuery->$filterMethodName($filterValue);
        }

        if ($secondGroupQuery instanceof Alive && !in_array($this->getFormModel()->secondGroup, [Group::BY_DATES, Group::BY_WEEK_NUMBERS, Group::BY_MONTH_NUMBERS], false)) {
          // для живых подписок мы должны брать самые актуальные значения
          // но если группируем не по временным полям, то применяем обычный фильтр
          $secondGroupQuery->handleFilterByDates($dateTo, $dateTo, $this->getFormModel()->ltvDateTo);
        } elseif ($secondGroupQuery instanceof Alive30) {
          // для живых подписок мы должны брать самые актуальные значения
          $secondGroupQuery->handleFilterByDates($dateTo, $dateTo, $this->getFormModel()->ltvDateTo);
        } else {
          $secondGroupQuery->handleFilterByDates($dateFrom, $dateTo, $this->getFormModel()->ltvDateTo);
        }

        $secondGroupQuery->handleFilterByHour($this->getFormModel()->hour);

        if ($this->getFormModel()->getPermissionsChecker()->canGroupByManagers()) {
          $secondGroupQuery->handleFilterByManagers($this->getFormModel()->manager);
        }
        if ($this->getFormModel()->getPermissionsChecker()->canFilterByLandingPayTypes()) {
          $secondGroupQuery->handleFilterByLandingPayTypes($this->getFormModel()->landingPayTypes);
        }
        if ($this->getFormModel()->getPermissionsChecker()->canFilterByProviders()) {
          $secondGroupQuery->handleFilterByProviders($this->getFormModel()->providers);
        }
        if ($this->getFormModel()->getPermissionsChecker()->canFilterByUsers()) {
          $secondGroupQuery->handleFilterByUsers($this->getFormModel()->users);
        }
        if ($this->getFormModel()->getPermissionsChecker()->canFilterByStreams()) {
          $secondGroupQuery->handleFilterByStreams($this->getFormModel()->streams);
        }
        if ($this->getFormModel()->getPermissionsChecker()->canFilterBySources()) {
          $secondGroupQuery->handleFilterBySources($this->getFormModel()->sources);
        }
        if ($this->getFormModel()->getPermissionsChecker()->canFilterByLandings()) {
          $secondGroupQuery->handleFilterByLandings($this->getFormModel()->landings);
        }
        if ($this->getFormModel()->getPermissionsChecker()->canFilterByLandingCategories()) {
          $secondGroupQuery->handleFilterByLandingCategories($this->getFormModel()->landingCategories);
        }
        if ($this->getFormModel()->getPermissionsChecker()->canFilterByOfferCategories()) {
          $secondGroupQuery->handleFilterByOfferCategories($this->getFormModel()->offerCategories);
        }
        if ($this->getFormModel()->getPermissionsChecker()->canFilterByPlatform()) {
          $secondGroupQuery->handleFilterByPlatforms($this->getFormModel()->platforms);
        }
        if ($this->getFormModel()->getPermissionsChecker()->canFilterByFakeRevshare()) {
          $secondGroupQuery->handleFilterByFake($this->getFormModel()->isFake);
        }
        if ($this->getFormModel()->getPermissionsChecker()->canFilterByCountries()) {
          $secondGroupQuery->handleFilterByCountries($this->getFormModel()->countries);
        }
        if ($this->getFormModel()->getPermissionsChecker()->canFilterByOperators()) {
          $secondGroupQuery->handleFilterByOperators($this->getFormModel()->operators);
        }

        foreach ($secondGroupQuery->all() as $secondItem) {
          $secondGroups = [];
          $secondGroup = $this->getFormModel()->secondGroup;
          $secondGroups[$secondGroup] = new Group($secondGroup, $secondItem[$secondGroup], $this->getFormModel());

          $key = $filterValue . implode('-', array_map(function (Group $secondGroup) {
            return $secondGroup->getValue();
          }, $secondGroups));

          if (!isset($rowModels[$key]) || !($rowModels[$key] instanceof Row)) {
            $rowModels[$key] = Yii::createObject([
              'class' => $this->rowClass,
              'currency' => $this->getFormModel()->currency
            ]);
          }
          $rowModels[$key]->setGroup($key);
          $rowModels[$key]->groups = $groups;
          $rowModels[$key]->secondGroup = $secondGroups;

          foreach ($secondItem as $secondItemKey => $secondItemValue) {
            $property = lcfirst(BaseInflector::camelize($secondItemKey));
            if (!property_exists($rowModels[$key]->rowDataDto, $property)) {
              // Если нет такого поля, значит это группировка. Идем дальше
              continue;
            }
            $rowModels[$key]->rowDataDto->$property = $secondItemValue;

          }
        }
      }
    }
    return $rowModels;
  }

  /**
   * @return array
   */
  private function getGroupSorting()
  {
    if (!$this->getFormModel()->hasGroupBy([Group::BY_COUNTRIES, Group::BY_PROVIDERS, Group::BY_OPERATORS])) {
      return ['asc' => ['groups' => SORT_ASC], 'desc' => ['groups' => SORT_DESC]];
    }

    return ['asc' => ['sortValue' => SORT_ASC], 'desc' => ['sortValue' => SORT_DESC]];
  }

  /**
   * @return int
   */
  private function getDefaultSortingDirection()
  {
    if (!$this->getFormModel()->hasGroupBy([Group::BY_COUNTRIES, Group::BY_PROVIDERS, Group::BY_OPERATORS])) {
      return SORT_DESC;
    }

    return SORT_ASC;
  }
}
