<?php

namespace mcms\holds\models;

use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserBalancesGroupedByDay;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Search модель для PartnerHold
 */
class PartnerHoldSearch extends PartnerHold
{
  public $dateFrom;
  public $dateTo;
  public $lastUnholdDateFrom;
  public $lastUnholdDateTo;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return array_merge(parent::rules(), [
      [['dateFrom', 'dateTo', 'lastUnholdDateFrom', 'lastUnholdDateTo'], 'date', 'format' => 'php:Y-m-d'],
      [['userCurrency'], 'each', 'rule' => ['string']],
    ]);
  }

  /**
   * @param array $params
   * @return ArrayDataProvider
   */
  public function search($params)
  {
    $dataProvider = new ArrayDataProvider([
      'sort' => [
        'attributes' => [
          'date',
          'countryId',
          'unholdDate',
          'holdProfit',
          'userCurrency',
          'lastUnholdDate',
        ],
        'defaultOrder' => [
          'date' => SORT_DESC,
        ],
      ]
    ]);

    $this->load($params);

    if (!$this->validate()) {
      return $dataProvider;
    }

    $dataProvider->allModels = $this->findAll();

    return $dataProvider;
  }

  /**
   * Превращаем результат запроса в массив объектов
   * @return self[]
   */
  public function findAll()
  {
    $result = [];
    foreach ($this->getAllProfit() as $row) {
      $model = new PartnerHold(['userId' => $this->userId]);
      $model->setAttributes($row);
      $result[] = $model;
    }
    return $result;
  }

  /**
   * Общий профит в холде (user_balances_grouped_by_day + user_balance_invoices)
   * @return array
   */
  private function getAllProfit()
  {
    $result = [];
    foreach ($this->findBalances()->each() as $row) {
      $key = $row['date'] . '_' . $row['countryId'] . '_' . $row['userCurrency'];
      $result[$key] = $row;
    }
    foreach ($this->findInvoices()->each() as $row) {
      $key = $row['date'] . '_' . $row['countryId'] . '_' . $row['userCurrency'];
      if (empty($result[$key])) {
        $result[$key] = $row;
        continue;
      }
      $result[$key]['holdProfit'] += $row['holdProfit'];
    }
    // Если общий профит в холде - 0, не показываем. Может быть при конвертации, когда баланс-инвойсы=0
    return array_filter($result, function ($value) {
      return ArrayHelper::getValue($value, 'holdProfit', 0) != 0;
    });
  }

  /**
   * @return Query
   */
  private function findBalances()
  {
    $query = (new Query())->select([
      'date' => 'ubgbd.date',
      'lastUnholdDate' => 'pcu.last_unhold_date',
      'countryId' => 'ubgbd.country_id',
      'userCurrency' => 'ubgbd.user_currency',
      'holdProfit' => new Expression(
        'SUM(
                        IF(
                          ubgbd.user_currency = "rub", 
                          ubgbd.profit_rub, 
                          IF(
                            ubgbd.user_currency = "usd", 
                            ubgbd.profit_usd, 
                            IF(ubgbd.user_currency = "eur", ubgbd.profit_eur, 0)
                          )
                        )
                      )')
    ])->from(UserBalancesGroupedByDay::tableName() . ' ubgbd')
      ->leftJoin('partner_country_unhold pcu', 'ubgbd.country_id = pcu.country_id AND ubgbd.user_id = pcu.user_id')
      ->where([
        'OR',
        ['>', 'ubgbd.date', new Expression('pcu.last_unhold_date')],
        ['pcu.last_unhold_date' => null]
      ])
      ->andWhere(['<>', 'ubgbd.country_id', 0])
      ->andWhere([
        'ubgbd.user_id' => $this->userId
      ])
      ->groupBy([
        'ubgbd.date', 'ubgbd.country_id', 'ubgbd.user_currency'
      ])
      ->andFilterWhere([
        'ubgbd.country_id' => $this->countryId,
        'ubgbd.user_currency' => $this->userCurrency,
      ]);

    $query->andFilterWhere(['>=', 'ubgbd.date', $this->dateFrom]);
    $query->andFilterWhere(['<=', 'ubgbd.date', $this->dateTo]);
    $query->andFilterWhere(['>=', 'pcu.last_unhold_date', $this->lastUnholdDateFrom]);
    $query->andFilterWhere(['<=', 'pcu.last_unhold_date', $this->lastUnholdDateTo]);

    return $query;
  }

  /**
   * @return Query
   */
  private function findInvoices()
  {
    $query = (new Query())->select([
      'date' => 'ubi.date',
      'lastUnholdDate' => 'pcu.last_unhold_date',
      'countryId' => 'ubi.country_id',
      'userCurrency' => 'ubi.currency',
      'holdProfit' => new Expression('SUM(ubi.amount)')
    ])->from(UserBalanceInvoice::tableName() . ' ubi')
      ->leftJoin('partner_country_unhold pcu', 'ubi.country_id = pcu.country_id AND ubi.user_id = pcu.user_id')
      ->where([
        'OR',
        ['>', 'ubi.date', new Expression('pcu.last_unhold_date')],
        ['pcu.last_unhold_date' => null]
      ])
      ->andWhere(['<>', 'ubi.country_id', 0])
      ->andWhere([
        'ubi.user_id' => $this->userId
      ])
      ->groupBy([
        'ubi.date', 'ubi.country_id', 'ubi.currency'
      ])
      ->andFilterWhere([
        'ubi.country_id' => $this->countryId,
        'ubi.currency' => $this->userCurrency,
      ]);

    $query->andFilterWhere(['>=', 'ubi.date', $this->dateFrom]);
    $query->andFilterWhere(['<=', 'ubi.date', $this->dateTo]);
    $query->andFilterWhere(['>=', 'pcu.last_unhold_date', $this->lastUnholdDateFrom]);
    $query->andFilterWhere(['<=', 'pcu.last_unhold_date', $this->lastUnholdDateTo]);

    return $query;
  }
}
