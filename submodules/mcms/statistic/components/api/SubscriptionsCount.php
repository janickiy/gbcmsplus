<?php

namespace mcms\statistic\components\api;

use Yii;
use yii\db\Query;
use yii\db\Expression;
use yii\base\InvalidParamException;
use mcms\common\module\api\join\Query as JoinQuery;
use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\common\exceptions\ParamRequired;

class SubscriptionsCount extends ApiResult
{

  /**
   *
   * @var array
   */
  private $_userIds;

  const TABLE_SEARCH_SUBSCRIPTIONS = 'search_subscriptions';
  const TABLE_SUBSCRIPTION_OFFS = 'subscription_offs';
  const TABLE_SOLD_SUBSCRIPTIONS = 'sold_subscriptions';

  public function init($params = array())
  {
    $this->_userIds = ArrayHelper::getValue($params, 'userIds');

    if ($this->_userIds === null) {
      $exception = new ParamRequired('userIds');
      $exception->setParamField('userIds');
      throw $exception;
    }

    $this->_userIds = is_array($this->_userIds) ? $this->_userIds : [$this->_userIds];

    return $this;
  }

  /**
   * Получение запроса числа подписок из таблицы
   * @param string $tableName
   * @return Query
   */
  protected function getCountByTable($tableName)
  {
    return (new Query())->select(['user_id', 'cnt' => new Expression('count(*)')])
    ->from($tableName)->andWhere(['user_id' => $this->_userIds])
    ->groupBy('user_id');
  }

  /**
   * Заполнение массива количеств значениями
   * @param Query $query
   * @return array
   */
  protected function fillValue(Query $query)
  {
    $result = array_fill_keys($this->_userIds, 0);

    foreach ($query->each() as $userData) {
      $result[(int) $userData['user_id']] = $userData['cnt'];
    }

    return $result;
  }

  /**
   * Получение количества всех подписок для пользователей
   * @return array
   */
  public function getSearchSubscriptionsCount()
  {
    return $this->fillValue($this->getCountByTable(self::TABLE_SEARCH_SUBSCRIPTIONS));
  }

  /**
   * Получение количества проданных подписок для пользователей
   * @return array
   */
  public function getSoldSubscriptionsCount()
  {
    return $this->fillValue($this->getCountByTable(self::TABLE_SOLD_SUBSCRIPTIONS));
  }

  /**
   * Получение количества проданных подписок для пользователей
   * @return array
   */
  public function getSubscriptionOffsCount()
  {
    $query = new Query();
    $query->addSelect(['cnt' => new Expression('count(*)')])->from(self::TABLE_SUBSCRIPTION_OFFS);

    $joinQuery = new JoinQuery($query, self::TABLE_SUBSCRIPTION_OFFS, ['INNER JOIN', 'source_id', '=', 'src'], ['user_id' => 'user_id']);
    Yii::$app->getModule('promo')->api('source', ['hash' => ''])->join($joinQuery);

    $query->andWhere(['user_id' => $this->_userIds])->groupBy(['user_id']);

    return $this->fillValue($query);
  }

  /**
   * Получение количества проданных подписок для пользователей
   * @return array
   */
  public function getActiveSubscriptionsCount()
  {
    $searchSubscriptions = $this->getSearchSubscriptionsCount();
    $subscriptionsSold = $this->getSoldSubscriptionsCount();
    $offSubscriptions = $this->getSubscriptionOffsCount();

    foreach ($searchSubscriptions as $userId => &$value) {
      $value -= $subscriptionsSold[$userId] + $offSubscriptions[$userId];
    }

    return $searchSubscriptions;
  }
}