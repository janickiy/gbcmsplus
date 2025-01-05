<?php

namespace mcms\promo\components;

use mcms\promo\components\api\UserPromoSettings;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use mcms\promo\models\TrafficBlock;

/**
 * Компонент проверяет разрешено ли партнеру лить на оператора
 * TRICKY такой же в микросервисе
 */
class TrafficBlockChecker
{
  private $operatorId;
  private $userId;

  /**
   * @var array Кэш по юзерам формата [{user_id} => [isBlacklistTrafficBlocks => true|false, operatorIds => [3,4,5]]]
   * В массиве operatorIds список блэклиста или вайтлиста в зависимости от настройки пользователя
   */
  private static $usersBlacklistCache = [];

  /**
   * @param $userId
   * @param $operatorId
   */
  public function __construct($userId, $operatorId)
  {
    $this->userId = (int)$userId;
    $this->operatorId = $operatorId;
  }

  /**
   * @return bool
   */
  public function isTrafficBlocked()
  {
    $settings = ArrayHelper::getValue(self::$usersBlacklistCache, $this->userId);

    if (!$settings) {
      self::$usersBlacklistCache[$this->userId] = [
        'isBlacklistTrafficBlocks' => (new UserPromoSettings())->getIsBlacklistTrafficBlocks($this->userId),
      ];

      self::$usersBlacklistCache[$this->userId]['operatorIds'] = $this->getOperatorIds(
        self::$usersBlacklistCache[$this->userId]['isBlacklistTrafficBlocks']
      );

      $settings = self::$usersBlacklistCache[$this->userId];
    }

    $isCurrentOperatorInList = in_array($this->operatorId, $settings['operatorIds'], false);

    return $settings['isBlacklistTrafficBlocks'] ? $isCurrentOperatorInList : !$isCurrentOperatorInList;
  }

  /**
   * Получить список операторов из правил для юзера с флагом $isBlacklistTrafficBlocks
   * @param bool $isBlacklistTrafficBlocks
   * @return int[]
   */
  private function getOperatorIds($isBlacklistTrafficBlocks)
  {
    $fromDb = (new Query())
      ->select(['operator_id'])
      ->from(TrafficBlock::tableName())
      ->andWhere([
        'user_id' => $this->userId,
        'is_blacklist' => $isBlacklistTrafficBlocks,
      ])
      ->column();

    return array_map('intval', $fromDb);
  }
}
