#!/usr/bin/env php
<?php

define('QUEUE_DEBUG', true);
define('DAEMON', true);

require(__DIR__ . '/../vendor/autoload.php');

/** Чтобы действовали правильные неймспейсы */
require(__DIR__ . '/../submodules/mcms/common/Yii.php');
require(__DIR__ . '/../common/config/bootstrap.php');
require(__DIR__ . '/../console/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
  require(__DIR__ . '/../common/config/main.php'),
  require(__DIR__ . '/../common/config/main-local.php'),
  require(__DIR__ . '/../console/config/main.php'),
  require(__DIR__ . '/../console/config/main-local.php')
);

$application = new yii\console\Application($config);

pcntl_signal(SIGTERM, 'sigHandler');
pcntl_signal(SIGHUP, 'sigHandler');
pcntl_signal(SIGINT, 'sigHandler');
function sigHandler($signal)
{
  global $stop;
  switch ($signal) {
    case SIGTERM:
      echo 'SIGTERM' . PHP_EOL;
      $stop = true;
      break;
    case SIGINT:
      echo 'SIGINT' . PHP_EOL;
      $stop = true;
      break;
    case SIGHUP:
      echo 'SIGHUP' . PHP_EOL;
      $stop = true;
      break;
  }
}

global $stop;
$stop = false;
$chanelName = end($argv);

if (in_array($chanelName, ['-h', '--help'])) {
  echo 'Usages' . PHP_EOL;
  printf('[php] %s [%s]' . PHP_EOL, $argv[0], implode(',', [
    \mcms\statistic\components\queue\postbacks\Worker::CHANNEL_NAME,
    \mcms\promo\components\queue\prelands\Worker::CHANNEL_NAME,
    \mcms\promo\queues\PartnerProgramSyncWorker::CHANNEL_NAME,
    \mcms\holds\queues\RuleUnholdPlannerWorker::CHANNEL_NAME,
    \mcms\notifications\components\invitations\queue\BuilderWorker::CHANNEL_NAME,
  ]));
  echo PHP_EOL;
  exit();
}

if ($chanelName == $argv[0]) {
  exec('./' . $argv[0] . ' --help', $output);
  echo implode(PHP_EOL, $output) . PHP_EOL;
  exit();
}

$worker = null;
switch ($chanelName) {
  case \mcms\statistic\components\queue\postbacks\Worker::CHANNEL_NAME:
    $worker = new \mcms\statistic\components\queue\postbacks\Worker();
    break;
  case \mcms\promo\components\queue\prelands\Worker::CHANNEL_NAME:
    $worker = new \mcms\promo\components\queue\prelands\Worker();
    break;
  case \mcms\promo\queues\PartnerProgramSyncWorker::CHANNEL_NAME:
    $worker = new \mcms\promo\queues\PartnerProgramSyncWorker;
    break;
  case \mcms\holds\queues\RuleUnholdPlannerWorker::CHANNEL_NAME:
    $worker = new \mcms\holds\queues\RuleUnholdPlannerWorker();
    break;
  case \mcms\notifications\components\invitations\queue\BuilderWorker::CHANNEL_NAME:
    $worker = new \mcms\notifications\components\invitations\queue\BuilderWorker();
    break;
  default:
    exit("Wrong chanel name\n");
}

if ($worker === null) {
  exit("Worker for chanel $chanelName not found\n");
}

/** @var rgk\queue\QueueFacade $facade */
$facade = Yii::$app->get('queue');

$listener = new \rgk\queue\listener\Listener($facade, $worker);
/** @var integer Время последнего обращения к бд */
$lastDbConnectTime = 0;
/** @var integer Интервал обращения к бд */
$dbConnectPeriod = 15;

while (!$stop) {
  if ($listener->handle(\rgk\queue\driver\RabbitMQ::DRIVER_CODE) === null) {
    //TRICKY для поддержания постоянного коннекта делаем запрос к бд каждые 15 сек
    if (time() > $lastDbConnectTime + $dbConnectPeriod) {
      $application->db->createCommand('SELECT 1')->execute();
      $lastDbConnectTime = time();
    }
    usleep(200000);
  }
  pcntl_signal_dispatch();
}