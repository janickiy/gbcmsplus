<?php

namespace mcms\statistic\commands;

use mcms\common\traits\LogTrait;
use mcms\statistic\components\subid\BaseHandler;
use mcms\statistic\components\subid\RegularUpdateConfig;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Обновление статы по меткам. Аггрегируем и апдейтим
 */
class SubidController extends Controller
{
  use LogTrait;

  /**
   * @var string Дата в формате 2017-11-24 по которой фильтруем хиты для генерации подписок.
   * Можно писать текстом, например "-2 days"
   * По-умолчанию "-1 day".
   */
  public $dateFrom;

  /**
   * @var string Дата в формате 2017-11-24 по которой фильтруем хиты для генерации подписок.
   * Можно писать текстом, например "-2 days"
   * По-умолчанию "today".
   */
  public $dateTo;

  /**
   * @var int|string Сделать обновление индивидуально какому-то юзеру (или можно указывать через запятую).
   * По-умолчанию не заполнено, будут использованы все таблицы юзеры
   */
  public $userId;

  /**
   * @var string Какие хэндлеры запускать (можно через запятую)
   */
  public $handlers;

  public $d;

  /**
   * @var RegularUpdateConfig
   */
  protected $cfg;

  public $isProfilerEnabled = false;


  public function actionIndex()
  {
    $this->cfg = Yii::createObject([
      'class' => RegularUpdateConfig::class,
      'dateFrom' => $this->dateFrom,
      'dateTo' => $this->dateTo,
      'userIds' => $this->userId,
      'maxTime' => time(),
    ]);

    $this->stdout("\tDate from: '{$this->cfg->getDateFrom()}'\n", Console::FG_GREEN);
    $this->stdout("\tDate to: '{$this->cfg->getDateTo()}'\n", Console::FG_GREEN);

    $this->dropTables();
    $this->createTables();

    foreach ($this->getHandlers() as $handler) {
      $this->logWithTime("{$handler::getName()}...");
      $handler->run();
      $this->log(PHP_EOL);
    }

    if ($this->isProfilerEnabled == true) {
      $dbLog = Yii::getLogger()->getProfiling(['yii\db*']);
      foreach ($dbLog as $query) {
        $this->stdout($query['info'] . "\n" . round($query['duration'], 5) . ' sec', Console::FG_GREY);
      }
    }
  }


  /**
   * @inheritdoc
   */
  public function options($actionID)
  {
    $parentOptions = parent::options($actionID);
    return ArrayHelper::merge($parentOptions, [
      'dateFrom',
      'dateTo',
      'userId',
      'handlers',
      'd',
      'isProfilerEnabled'
    ]);
  }

  /**
   * @return string[]
   */
  protected function getHandlerNames()
  {
    if ($this->handlers) {
      return explode(',', $this->handlers);
    }

    return [
      'Glossary',
      'ClickhouseImportHits',
      'ClickhouseImportSubscriptions',
      'ClickhouseImportSoldSubscriptions',
      'ClickhouseImportSubscriptionRebills',
      'Hits',
      'Subscriptions',
      'CorrectedRebills',
      'CorrectedRebills24',
      'Rebills',
      'Rebills24',
      'Unsubscriptions',
      'Unsubscriptions24',
      'Buyouts',
      'Onetime',
      'Complaints',
      'Refunds',
      'AddFields',
      'SearchSubscriptions',
      // ... пдп, и т.д.
    ];
  }

  /**
   * @return BaseHandler[]
   */
  protected function getHandlers()
  {
    $handlerObjects = [];

    foreach ($this->getHandlerNames() as $handlerName) {
      $handlerClass = 'mcms\statistic\components\subid\handlers\\' . $handlerName;

      $handlerObjects[$handlerName] = new $handlerClass(['cfg' => $this->cfg]);
    }

    return $handlerObjects;
  }

  private function createTables()
  {
    $this->logWithTime('Creating tables...' . PHP_EOL);

    $allUsers = $this->cfg->getUserIds();
    $processedUsers = Yii::$app->cache->get('createTables_processedUsers') ? : [];
    $needProcessUsers = array_diff($allUsers, $processedUsers);
    if (count($needProcessUsers) === 0) {
      return ;
    }

    $sql = file_get_contents(__DIR__ . '/../migrations/statistic_user_tables.sql');

    foreach ($needProcessUsers as $userId) {
      Yii::$app->sdb->createCommand(str_replace('{{userid}}', $userId, $sql))->execute();
    }

    Yii::$app->cache->set('createTables_processedUsers', $needProcessUsers, 0);
  }

  private function dropTables()
  {
    // todo delete please
    if ($this->d) {
      $bakTables = Yii::$app->sdb->createCommand('SELECT table_name
      FROM information_schema.tables
      where table_schema = DATABASE()
      AND (table_name like \'%statistic_user_%\') 
      ;')->queryColumn();
      foreach ($bakTables as $bakTable) {
        Yii::$app->sdb->createCommand("DROP TABLE $bakTable")->execute();
      }
    }
  }

  /**
   * @param $msg
   * @throws \yii\base\InvalidConfigException
   */
  protected function logWithTime($msg)
  {
    $this->log(Yii::$app->formatter->asDatetime('now'), [Console::FG_GREEN]);
    $this->log(' ' . $msg, [Console::BOLD]);
  }
}
