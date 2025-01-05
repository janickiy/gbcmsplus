<?php
namespace mcms\notifications\models\queries;


use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class GroupedNotificationsActiveQuery
 * @package mcms\notifications\models\queries
 */
class NotificationsActiveQuery extends ActiveQuery
{
  /**
   * добавляет сгруппированные по полю event данные о нотификациях
   */
  public function groupConcatNotifications()
  {
    $this->groupBy('event');
    $this->addSelect([
      'types' => new Expression('GROUP_CONCAT(notification_type)'),
      'ids' => new Expression('GROUP_CONCAT(notifications.id)'),
      'enabled' => new Expression('GROUP_CONCAT(ni.notification_id IS NULL)'),
    ]);
  }
}