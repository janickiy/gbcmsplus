<?php

namespace mcms\statistic\components;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use mcms\statistic\components\StatisticQuery;
use yii\helpers\ArrayHelper;
use mcms\common\module\api\join\Query as JoinQuery;

abstract class AbstractDetailStatistic extends AbstractStatistic
{
  private $_cpaDiffCalcDays;
  private $_userData;
  private $_partnerCurrency;

  const SOLD_STAT = 'solds';

  /**
   * @param Query $query
   * @return Query
   */
  function addQueryJoins(Query $query)
  {
    $fields = $this->getJoinFields();

    /** @var \mcms\promo\components\api\Source $sourceApi */
    $sourceApi = Yii::$app->getModule('promo')->api('source');
    $joinQuery1 = new JoinQuery(
      $query,
      'st',
      ['LEFT JOIN', $fields['source'], '=', 'source'],
      [
        'source_name' => 'source.name',
        'source_url' => 'source.url',
        'source_type' => 'source.source_type',
        'source_id' => 'source.id'
      ]
    );
    $sourceApi->join($joinQuery1);

    /* @var $query StatisticQuery*/
    if ($query->getId() === self::SOLD_STAT) {
      /** @var \mcms\promo\components\api\Source $sourceApi */
      $sourceApi = Yii::$app->getModule('promo')->api('source');
      $joinQuery2 = new JoinQuery(
        $query,
        'from_sources',
        ['LEFT JOIN', $fields['from_sources'], '=', 'from_sources'],
        [
          'source_name' => 'IF(sold.id IS NULL, source.name, from_sources.name)',
          'source_url' => 'IF(sold.id IS NULL, source.url, from_sources.url)',
          'source_type' => 'IF(sold.id IS NULL, source.source_type, from_sources.source_type)',
          'source_id' => 'IF(sold.id IS NULL, source.id, from_sources.id)',
        ]
      );
      $sourceApi->join($joinQuery2);
    }


    /** @var \mcms\promo\components\api\StreamList $streamApi */
    $streamApi = Yii::$app->getModule('promo')->api('streams');
    $joinQuery3 = new JoinQuery(
      $query,
      'st',
      ['LEFT JOIN', $fields['stream'], '=', 'stream'],
      [
        'stream_name' => 'stream.name',
        'stream_id' => 'stream.id'
      ]
    );
    $streamApi->join($joinQuery3);

    /* @var $query StatisticQuery*/
    if ($query->getId() === self::SOLD_STAT) {
      /** @var \mcms\promo\components\api\StreamList $streamApi */
      $streamApi = Yii::$app->getModule('promo')->api('streams');
      $joinQuery4 = new JoinQuery(
        $query,
        'from_streams',
        ['LEFT JOIN', $fields['from_streams'], '=', 'from_streams'],
        [
          'stream_name' => 'IF(sold.id IS NULL, stream.name, from_streams.name)',
          'stream_id' => 'IF(sold.id IS NULL, stream.id, from_streams.id)',
        ]
      );
      $streamApi->join($joinQuery4);
    }

    /** @var \mcms\promo\components\api\OperatorList $operatorApi */
    $operatorApi = Yii::$app->getModule('promo')->api('operators');
    $joinQuery5 = new JoinQuery(
      $query,
      'st',
      ['LEFT JOIN', $fields['operator'], '=', 'operator'],
      [
        'operator_name' => 'operator.name',
        'operator_id' => 'operator.id'
      ]
    );
    $operatorApi->join($joinQuery5);

    /** @var \mcms\promo\components\api\CountryList $countryApi */
    $countryApi = Yii::$app->getModule('promo')->api('countries');
    $joinQuery6 = new JoinQuery(
      $query,
      'st',
      ['LEFT JOIN', $fields['country'], '=', 'country'],
      [
        'country_name' => 'country.name',
        'country_id' => 'country.id'
      ]
    );
    $countryApi->join($joinQuery6);

    /** @var \mcms\promo\components\api\PlatformList $platformApi */
    $platformApi = Yii::$app->getModule('promo')->api('platforms');
    $joinQuery7 = new JoinQuery(
      $query,
      'st',
      ['LEFT JOIN', $fields['platform'], '=', 'platform'],
      [
        'platform_name' => 'platform.name',
        'platform_id' => 'platform.id'
      ]
    );
    $platformApi->join($joinQuery7);

    /** @var \mcms\promo\components\api\LandingList $landingApi */
    $landingApi = Yii::$app->getModule('promo')->api('landings');
    $joinQuery8 = new JoinQuery(
      $query,
      'st',
      ['LEFT JOIN', $fields['landing'], '=', 'l'],
      [
        'landing_name' => 'l.name',
        'landing_id' => 'l.id',
      ]
    );
    $landingApi->join($joinQuery8);

    /** @var \mcms\promo\components\api\LandingPayTypeList $payTypesApi */
    $payTypesApi = Yii::$app->getModule('promo')->api('payTypes');
    $joinQuery9 = new JoinQuery(
      $query,
      'st',
      ['LEFT JOIN', $fields['payType'], '=', 'paytype'],
      ['landing_pay_type_name' => 'paytype.name']
    );
    $payTypesApi->join($joinQuery9);

    /** @var \mcms\user\components\api\User $userApi */
    $userApi = Yii::$app->getModule('users')->api('user');
    $joinQuery10 = new JoinQuery(
      $query,
      'u',
      ['LEFT JOIN', $fields['user'], '=', 'u'],
      ['user_id' => 'u.id']
    );
    $userApi->join($joinQuery10);

    /* @var $query StatisticQuery*/
    if ($query->getId() === self::SOLD_STAT) {
      /** @var \mcms\user\components\Api\User $api */
      $api = Yii::$app->getModule('users')->api('user');
      $joinQuery11 = new JoinQuery(
        $query,
        'from_users',
        ['LEFT JOIN', $fields['from_users'], '=', 'from_users'],
        [
          'user_id' =>  'IF(sold.id IS NULL, st.user_id, from_users.id)',
          'email' => 'IF(sold.id IS NULL, u.email, from_users.email)',
        ]
      );
      $api->join($joinQuery11);
    }

    return $query;

  }

  /**
   * Поля для связи
   * @return string[]
   */
  protected function getJoinFields()
  {
    return [
      'source' => 'st.source_id',
      'stream' => 'st.stream_id',
      'country' => 'st.country_id',
      'operator' => 'st.operator_id',
      'platform' => 'st.platform_id',
      'landing' => 'st.landing_id',
      'payType' => 'st.landing_pay_type_id',
      'user' => 'st.user_id',
      'from_users' => 'sold.user_id',
      'from_sources' => 'sold.source_id',
      'from_streams' => 'sold.stream_id',
    ];
  }

  /**
   * Количество дней для расчета показа подписки парьнеру
   * @return int
   */
  protected function getCpaDiffCalcDays()
  {
    if ($this->_cpaDiffCalcDays) return $this->_cpaDiffCalcDays;

    /** @var \mcms\statistic\Module $statModule */
    $statModule = Yii::$app->getModule('statistic');

    return $this->_cpaDiffCalcDays = $statModule->getCpaDiffCalcDays();
  }

  /**
   * Получить валюту пользователя
   * @return int
   */
  protected function getPartnerCurrency($user_id)
  {
    if ($this->_partnerCurrency) return $this->_partnerCurrency;

    /** @var \mcms\payments\Module $paymentsModule */
    $paymentsModule = Yii::$app->getModule('payments');

    return $this->_partnerCurrency = $paymentsModule
      ->api('userSettingsData', ['userId' => $user_id])
      ->getResult()
      ->getCurrency();
  }

  /**
   * Получение данных из personal_profit
   * @param $gridRow
   * @return array
   */
  protected function getPartnerCPAData($gridRow)
  {
    /** @var \mcms\statistic\Module $promoModule */
    $promoModule = Yii::$app->getModule('promo');

    $user_id = ArrayHelper::getValue($gridRow, 'user_id');
    $operator_id = ArrayHelper::getValue($gridRow, 'operator_id');
    $landing_id = ArrayHelper::getValue($gridRow, 'landing_id');

    return $promoModule->api('personalProfit', [
      'userId' => $user_id,
      'operatorId' => $operator_id,
      'landingId' => $landing_id
    ])->getResult();
  }

  /**
   * @param array $gridRow
   * @return integer
   */
  abstract protected function isCpaVisible($gridRow);

  /**
   * Получение данных для проверки корректировок
   * @param $gridRow
   * @return array|mixed
   */
  public function getUserData($gridRow)
  {
    if ($this->_userData) return $this->_userData;

    $currency = $this->getPartnerCurrency($gridRow['user_id']);
    $diff = (float)$this->isCpaVisible($gridRow, $currency);
    $partnerProfit = $this->getPartnerCPAData($gridRow);

    return $this->_userData = [
      'currency' => $currency,
      'cpa_profit' => ArrayHelper::getValue($partnerProfit, 'cpa_profit_' . $currency),
      'date' => Yii::$app->formatter->asDate(ArrayHelper::getValue($partnerProfit, 'updated_at')),
      'period' => $this->getCpaDiffCalcDays(),
      'diff' => $diff,
      'is_show' => $diff >= 0
    ];
  }

  /**
   * Cписок доступных статусов видимости подписки
   * @return array
   */
  public function getVisibleStatuses()
  {
    return [
      1 => Yii::_t('statistic.statistic.is_visible'),
      0 => Yii::_t('statistic.statistic.is_invisible')
    ];
  }

  /**
   * Cписок типов профитов
   * @return array
   */
  public function getProfitTypes()
  {
    return [
      'revshare' => Yii::_t('statistic.statistic.profit_type_revshare'),
      'rejected' => Yii::_t('statistic.statistic.profit_type_rejected'),
      'sold'  => Yii::_t('statistic.statistic.profit_type_sold')
    ];
  }

}