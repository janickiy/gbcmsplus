<?php

namespace mcms\notifications\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Тут храняться нотификации которые пользователь не хочет получать
 * Class NotificationsIgnore
 * @package mcms\notifications\models
 *
 * @property int notification_id
 * @property int user_id
 */
class NotificationsIgnore extends ActiveRecord
{
  /**
   * @return string
   */
  public static function tableName()
  {
    return 'notifications_ignore';
  }

  /**
   * Добавить все нотификции определенного типа в игнорируемые
   * @param int $userId
   * @param int $type
   * @param int|null $moduleId
   * @return int
   */
  public static function ignoreAll($userId, $type, $moduleId = null)
  {
    $params = [
      ':notification_type' => $type,
      ':userId' => $userId,
    ];

    $additionalCondition = '';
    if ($moduleId ) {
      $additionalCondition =  'AND module_id = :module_id';
      $params[':module_id'] = $moduleId;
    }


    return Yii::$app->db->createCommand('
      INSERT IGNORE INTO ' . self::tableName() . '(user_id, notification_id)
      SELECT :userId as user_id, id as notification_id
      FROM ' . Notification::tableName() . ' 
      WHERE notification_type = :notification_type ' . $additionalCondition
    )->bindValues($params)->execute();
  }

  /**
   * Убрать все нотификации определенного типа из игнорируемых
   * @param int $userId
   * @param int $type
   * @param int|null $moduleId
   * @return int
   */
  public static function noticeAll($userId, $type, $moduleId = null)
  {
    $params = [
      ':notification_type' => $type,
      ':user_id' => $userId,
    ];

    $additionalCondition = '';
    if ($moduleId ) {
      $additionalCondition =  'AND n.module_id = :module_id';
      $params[':module_id'] = $moduleId;
    }
    return Yii::$app->db->createCommand('
      DELETE ni.* FROM ' . self::tableName() . ' ni
      INNER JOIN ' . Notification::tableName() . ' n 
        ON n.id = ni.notification_id 
          AND ni.user_id = :user_id 
          AND n.notification_type = :notification_type ' .
      $additionalCondition
    )->bindValues($params)->execute();
  }
}