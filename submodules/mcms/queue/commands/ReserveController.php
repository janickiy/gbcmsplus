<?php

namespace mcms\queue\commands;

use rgk\queue\driver\MySQL;
use rgk\queue\driver\RabbitMQ;
use Yii;
use yii\console\Controller;

/**
 * Управление резервной очередью
 */
class ReserveController extends Controller
{
  /**
   * Восстановить задачи из резервной очереди
   */
  public function actionRecover()
  {
    $this->stdout("Start reserve queue recover...\n");

    /** @var MySQL $mysqlDriver */
    $mysqlDriver = Yii::$app->queue->getDriverByCode(MySQL::DRIVER_CODE);
    /** @var RabbitMQ $rabbitDriver */
    $rabbitDriver = Yii::$app->queue->getDriverByCode(RabbitMQ::DRIVER_CODE);
    $mysqlDriver->recoverAll($rabbitDriver);

    $this->stdout("Finish!\n");
  }
}