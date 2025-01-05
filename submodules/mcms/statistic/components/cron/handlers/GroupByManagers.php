<?php

namespace mcms\statistic\components\cron\handlers;


use mcms\statistic\components\cron\AbstractTableHandler;
use Yii;

/**
 * Запись данных в таблицу истории прикрепления менеджеров к партнерам. Используется при группировке по менеджерам
 * tricky: данные обновляются только за текущий день, не смотря на то, какие даты указаны в параметрах крона
 * tricky: Логика работы: если сменить менеджера, то стата за день, когда сменили будет засчитана новому менеджеру.
 * Если убрать менеджера, то за весь последний день стата будет засчитана менеджеру
 * @package mcms\statistic\components\cron\handlers
 */
class GroupByManagers extends AbstractTableHandler
{
  public function run()
  {
    Yii::$app->db->createCommand("INSERT INTO `partners_managers` 
          (user_id, manager_id, date) 
          (SELECT id, manager_id, :date FROM users WHERE manager_id IS NOT NULL)
        ON DUPLICATE KEY UPDATE
        manager_id = VALUES(manager_id)")
      ->bindValue(':date', Yii::$app->formatter->asDate('today', 'php:Y-m-d'))
      ->execute();
  }
}