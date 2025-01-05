<?php

namespace mcms\statistic\models\mysql;

use mcms\promo\models\Country;
use mcms\statistic\components\mainStat\BaseFetch;
use mcms\statistic\components\mainStat\FormModel;
use mcms\statistic\components\mainStat\Group;
use mcms\statistic\components\mainStat\mysql\Row;
use mcms\statistic\models\ResellerStatisticModel;
use Yii;
use yii\base\Object;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Модель для получения статистики реселлера
 */
class ResellerStatistic extends Object
{
  const STATISTIC_NAME = 'resellerStat';

  const GROUP_BY_CO_OP_US = [Group::BY_COUNTRIES, Group::BY_OPERATORS, Group::BY_USERS];
  const GROUP_BY_DATE_CO_OP_US = [Group::BY_DATES, Group::BY_COUNTRIES, Group::BY_OPERATORS, Group::BY_USERS];

  /** @var int */
  protected $_resellerId;
  /** @var string */
  public $dateFrom;
  /** @var string */
  public $dateTo;
  /** @var bool */
  public $isMonthly;

  /**
   * @return ArrayDataProvider
   */
  public function getDataProvider() {
    $resultArray = [];
    foreach (['rub', 'usd', 'eur'] as $currency) {
      $formModel = new FormModel([
        'viewerId' => $this->getResellerId(),
        'dateFrom' => $this->dateFrom,
        'dateTo' => $this->dateTo,
        'groups' => $this->isMonthly ? self::GROUP_BY_CO_OP_US : self::GROUP_BY_DATE_CO_OP_US,
        'currency' => $currency,
        'countries' => array_keys($this->getCurrencyCountries($currency)) ?: [null],
      ]);
      /* @var BaseFetch $fetch */
      $fetch = Yii::$container->get(BaseFetch::class, [$formModel]);

      $this->loadStatistic($resultArray, $fetch->getDataProvider()->allModels);
    }
    ksort($resultArray);

    return new ArrayDataProvider([
      'allModels' => $resultArray
    ]);
  }

  /**
   * @param array $resultArray
   * @param Row[] $models
   */
  private function loadStatistic(array &$resultArray, array $models)
  {
    foreach ($models as $model) {
      $groupByDates = ArrayHelper::getValue($model->getGroups(), Group::BY_DATES);
      $groupByCountries = ArrayHelper::getValue($model->getGroups(), Group::BY_COUNTRIES);
      $groupByOperators = ArrayHelper::getValue($model->getGroups(), Group::BY_OPERATORS);
      $groupByUsers = ArrayHelper::getValue($model->getGroups(), Group::BY_USERS);

      /** @var Row $model */
      $resultArray[$model->getGroup()] = new ResellerStatisticModel([
        'date' => $groupByDates ? $groupByDates->getFormattedValue() : null,
        'currency' => $model->getCurrency(),
        'countryCode' => $groupByCountries ? $groupByCountries->getFormattedValue() : null,
        'operator' => $groupByOperators ? $groupByOperators->getFormattedValue() : null,
        'user' => $groupByUsers ? $groupByUsers->getFormattedValue() : null,
        'resHits' => $model->getHits(),
        'resUniques' => $model->getUniques(),
        'resTb' => $model->getTb(),
        'resAccepted' => $model->getAccepted(),
        'resCpaAccepted' => $model->getCpaAccepted(),
        'resRevAccepted' => $model->getRevshareAccepted(),
        'resOnetimes' => $model->getOnetime(),
        'resSold' => $model->getSold(),
        'resSubs' => $model->getRevSub(),
        'resComplains' => $model->getComplains(),
        'resCalls' => $model->getCalls(),
        'resRevResSum' => $model->getRevshareResellerProfit() + $model->getRejectedProfit(),
        'resRevPartnerSum' => $model->getPartnerRevshareProfit() + $model->getRejectedProfit(),
        'resOffs' => $model->getOffs(),
        'resOffs24' => $model->getScopeOffsData(),
        'resRebills' => $model->getRebills() + $model->getRejectedRebills(),
        'resRebillsOnDate' => $model->getRebillsDateByDate(),
        'resProfitOnDate' => $model->getProfitDateByDate(),
        'resOnetimeResSum' => $model->getOnetimeResellerProfit(),
        'resOnetimePartnerSum' => $model->getOnetimeProfit(),
        'resVisibleSubscriptions' => $model->getSoldVisible() + $model->getVisibleOnetime(),

        'iSubs' => $model->getCpaOns() - $model->getRejectedOns(),
        'iOffs' => $model->getSoldOffs(),
        'iOffs24' => $model->getSoldScopeOffsData(),
        'iRebills' => $model->getSoldRebills(),
        'iRebillsOnDate' => $model->getSoldRebillsDateByDate(),
        'iProfitOnDate' => $model->getSoldProfitDateByDate(),
        'iTotalSum' => $model->getPartnerRevshareProfit() + $model->getCpaProfit(),
        'iBuyoutSum' => $model->getSoldPrice()
      ]);
    }
  }

  /**
   * @return int
   */
  private function getResellerId()
  {
    if ($this->_resellerId) return $this->_resellerId;
    /** @var \mcms\user\Module $usersModule */
    $usersModule = Yii::$app->getModule('users');
    $reseller = $usersModule->api('usersByRoles', ['reseller'])->getResult();
    return $this->_resellerId = current($reseller)['id'];
  }

  /**
   * @param $currency
   * @return array
   */
  private function getCurrencyCountries($currency)
  {
    $countries = Country::find()->all();
    return ArrayHelper::getValue(
      ArrayHelper::map($countries, 'id', 'code', 'currency'),
      $currency,
      []
    );
  }


}