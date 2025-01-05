<?php

namespace mcms\promo\models\search;

use mcms\promo\models\SubscriptionsLimit;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Поиск лимитов подписок
 */
class SubscriptionLimitsSearch extends Model
{
  const DATE_RANGE_DELIMITER = ' - ';

  public $id;
  public $userId;
  public $operatorId;
  public $countryId;
  public $subscriptionsFrom;
  public $subscriptionsTo;
  public $createdAtRange;

  /**
   * @return array
   */
  public function rules()
  {
    return [
      [['id', 'countryId', 'userId', 'operatorId', 'subscriptionsFrom', 'subscriptionsTo'], 'integer'],
      [['createdAtRange'], 'string'],
    ];
  }

  /**
   * Поиск лимитов подписок
   * @param array $params
   * @return ActiveDataProvider
   * @throws \yii\base\InvalidParamException
   */
  public function search($params)
  {
    $query = SubscriptionsLimit::find();
    $provider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'id' => SORT_DESC,
        ]
      ],
    ]);

    $this->load($params);
    if (!$this->validate()) {
      $query->where('0=1');
      return $provider;
    }

    $query->andFilterWhere([
      'and',
      ['id' => $this->id],
      ['operator_id' => $this->operatorId],
      ['country_id' => $this->countryId],
      ['user_id' => $this->userId],
      ['>=', 'subscriptions_limit', $this->subscriptionsFrom],
      ['<=', 'subscriptions_limit', $this->subscriptionsTo],
    ]);

    if (!empty($this->createdAtRange) && strpos($this->createdAtRange, '-') !== false) {
      list($startDate, $endDate) = explode(self::DATE_RANGE_DELIMITER, $this->createdAtRange);
      $query->andFilterWhere([
        'between',
        'created_at',
        strtotime($startDate),
        strtotime($endDate . ' +1day') - 1
      ]);
    }

    return $provider;
  }
}