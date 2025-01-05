<?php

namespace mcms\promo\components;

use mcms\promo\components\api\UserPromoSettings;
use mcms\promo\models\Operator;
use mcms\promo\models\TrafficBlock;
use yii\db\Query;

/**
 * Возвращает список id доступных партнеру операторов
 */
class AvailableOperators
{
  protected $userId;
  /** @var AvailableOperators[] */
  protected static $_single = [];

  /**
   * @var array
   */
  protected static $_trafficBlockOperators = [];

  /**
   * @var array
   */
  protected static $_activeOperators = [];

  private function __construct($userId)
  {
    $this->userId = (int)$userId;
  }

  /**
   * Почти синглтон
   * @param $userId
   * @return AvailableOperators
   */
  public static function getInstance($userId)
  {
    if (empty(self::$_single[$userId])) {
      self::$_single[$userId] = new self($userId);
    }
    return self::$_single[$userId];
  }

  /**
   * Если установлен режим блеклиста, вернем все, кроме его содержимого, если вайтлист - вернем только его содержимое
   * @return array
   */
  public function getIds()
  {
    $result =  $this->isBlacklistTrafficBlocks()
      ? $this->getOperatorIds()
      : $this->getTrafficBlockOperatorIds();

    return array_map('intval', $result);
  }

  /**
   * Получить список операторов, кроме операторов из правил для юзера (если установлен режим блеклиста)
   * @return array
   */
  private function getOperatorIds()
  {
    if (!self::$_activeOperators) {
      self::$_activeOperators = (new Query())
        ->select('id')
        ->from(Operator::tableName())
        ->andWhere(['status' => Operator::STATUS_ACTIVE])
        ->column();
    }

    return array_diff(self::$_activeOperators, $this->getTrafficBlockOperatorIds());
  }

  /**
   * Получить список операторов из правил для юзера
   * @return array
   */
  private function getTrafficBlockOperatorIds()
  {
    $isBlacklistTrafficBlocks = $this->isBlacklistTrafficBlocks();

    $key = sprintf('%s.%s', $this->userId, (int) $isBlacklistTrafficBlocks);

    if (!isset(self::$_trafficBlockOperators[$key])) {
      $data = (new Query())
        ->select(['operator_id'])
        ->from(TrafficBlock::tableName())
        ->andWhere([
          'user_id' => $this->userId,
          'is_blacklist' => $this->isBlacklistTrafficBlocks(),
        ])
        ->column();

      self::$_trafficBlockOperators[$key] = $data;
      unset($data);
    }

    return self::$_trafficBlockOperators[$key];
  }

  /**
   * Получаем значение настройки is_blacklist_traffic_blocks
   * (true - используем черный список, false - белый)
   * @return bool
   */
  private function isBlacklistTrafficBlocks()
  {
    return (new UserPromoSettings())->getIsBlacklistTrafficBlocks($this->userId);
  }
}