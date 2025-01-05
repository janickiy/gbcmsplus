<?php

namespace mcms\statistic\commands;

use mcms\common\controller\ConsoleController;
use mcms\common\helpers\ArrayHelper;
use mcms\statistic\components\cron\AbstractTableHandler;
use mcms\statistic\components\events\RecalcStatEvent;
use Yii;
use yii\base\ExitException;
use yii\helpers\Console;
use mcms\statistic\components\cron\CronParams;

/**
 * Class CronController
 * @package mcms\statistic\commands
 */
class CronController extends ConsoleController
{
  const CACHE_KEY_STATISTIC_RECALC_DATE = 'cache_key_statistic_recalc_date';
  const CACHE_KEY_STATISTIC_RECALC_DATE_DURATION = 24 * 60 * 60;
  /** @var bool можно запустить этот крон через
   * php yii statistic/cron --launchBuyout
   * и после крона будет выполнен выкуп
   */
  public $launchBuyout = false;

  /**
   * В некоторых тестах используется вызов этого контроллера
   * Чтобы не обращать внимание на ограничения периода работы крона, ставим это свойство в тру
   * @var bool
   */
  public $ignoreDateRangeLimit = false;

  /**
   * можно включить профайлер запросов
   * @var bool
   */
  public $isProfilerEnabled = false;

  /**
   * Исключить хэндлеры (через запятую)
   * @var string
   */
  public $excludeHandlers;

  /**
   * @var bool разрешить ли старый обработчик
   * @see BalanceByUserAndDate
   */
  public $allowBalanceByUserAndDate = false;

  /**
   * @param int $hoursMinus - кол-во часов, которое вычетаем с момента последнего обновления.
   * @param null|string $specifyHandler
   * @throws \Exception
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\db\Exception
   */
  public function actionIndex($hoursMinus = 12, $specifyHandler = null)
  {
    $fromTime = null;
    $recalcFromCache = $this->getRecalcFromCache();

    if ($recalcFromCache === date('Y-m-d')) {
      $statisticRecalc = Yii::$app->db->createCommand('SELECT `trans_id`, `time` FROM `statistic_recalc` ORDER BY `time` ASC LIMIT 1')->queryOne();
      if ($statisticRecalc) {
        $fromTime = (int)$statisticRecalc['time'];
        $transId = $statisticRecalc['trans_id'];
        (new RecalcStatEvent($transId, $fromTime))->trigger();
        Yii::$app->db->createCommand('TRUNCATE `statistic_recalc`')->execute();
      }
      $this->setRecalcStatDateTommorrow();
    }

    if ($recalcFromCache === false) {
      $this->setRecalcStatDateTommorrow();
    }

    if (!$fromTime) {
      $fromTime = Yii::$app->db->createCommand('SELECT `date`, `hour` FROM `statistic` ORDER BY `date` DESC, `hour` DESC LIMIT 1')->queryOne();
      $fromTime = $fromTime
        ? Yii::$app->formatter->asTimestamp(sprintf('%s %s:00:00', $fromTime['date'], $fromTime['hour']))
        : 1262307661; // 1 января 2010 года берем как самую левую возможную дату

      if ($fromTime > time()) {
        $displayFromTime = date('Y-m-d H:i:s', $fromTime);
        $displayNowTime = date('Y-m-d H:i:s');
        $this->stdout("FromTime {$displayFromTime} is grater than now {$displayNowTime}. Revert to now time", Console::BG_CYAN);
        $fromTime = time();
      }
      $fromTime -= 3600 * (int)$hoursMinus;
    }

    // Максимальный диапазон - 2 недели
    $twoWeeksAgo = Yii::$app->formatter->asTimestamp('-2 weeks');
    if (!$this->ignoreDateRangeLimit && $fromTime < $twoWeeksAgo) {
      $this->stdout('Вы задали диапазон более 2-х недель (с ' . date('Y-m-d', $fromTime) . ').' . PHP_EOL .
        'Используйте настройку --ignoreDateRangeLimit' . PHP_EOL, Console::FG_RED);
      Yii::error('Пересчет крона статы с ' . date('Y-m-d', $fromTime) . ' запрещен.');
      //пересчитываем за 2 недели
      $fromTime = $twoWeeksAgo;
    }

    $params = new CronParams(['fromTime' => $fromTime, 'allowBalanceByUserAndDate' => $this->allowBalanceByUserAndDate]);

    $this->stdout('Today: ' . Yii::$app->formatter->asDatetime(time()), Console::FG_GREEN);
    $this->stdout('From time: ' . Yii::$app->formatter->asDatetime($params->fromTime), Console::FG_GREEN);

    $handlers = $specifyHandler ? [$specifyHandler] : $this->getHandlers();

    $profTime = microtime(true);

    foreach ($handlers as $handlerName) {
      $handlerClass = $this->getHandlerClass($handlerName);

      if (!class_exists($handlerClass)) {
        $this->stdout('Handler class not found ' . $handlerClass, Console::FG_RED);
        continue;
      }

      /** @var AbstractTableHandler $handler */
      $handler = new $handlerClass(['params' => $params]);
      if (!$handler instanceof AbstractTableHandler) {
        $this->stdout($handlerClass . ' must be instance of ' . AbstractTableHandler::class, Console::FG_RED);
        continue;
      }

      if (isset($specifyHandler) && $specifyHandler !== (new \ReflectionClass($handler))->getShortName()) {
        continue;
      }
      $handlerStartTime = microtime(true);

      $this->stdout('   START ' . $handler::class . '...', Console::FG_GREEN);
      $handler->run();

      $handlerTime = microtime(true) - $handlerStartTime;
      $this->stdout('   END ' . $handler::class . "... time: $handlerTime", Console::FG_GREEN);
    }

    $allTime = microtime(true) - $profTime;
    $this->stdout("   All time: $allTime\n", Console::FG_GREEN);

    // профилирование запросов
    if ($this->isProfilerEnabled == true) {
      $dbLog = Yii::getLogger()->getProfiling(['yii\db*']);
      foreach ($dbLog as $query) {
        $this->stdout($query['info'] . "\n" . round($query['duration'], 5) . ' sec', Console::FG_GREY);
      }
    }

    // запускаем выкуп если передали --launchBuyout
    if ($this->launchBuyout) {
      (new BuyoutController('buyout', $this->module))->actionIndex();
    }
  }

  /**
   * @return array
   */
  private function getHandlers()
  {
    $handlers = [
      'SearchSubscriptions',
      'Statistic',
      'HitsByHours',
      'HitsByDate',
      'SubscriptionsByHours',
      'SubscriptionsByDate',
      'StatisticByUserAndDate',
      'BalanceByUserAndDate',
      'BalanceByUserDateCountry',
      'StatFilters',
      'StatisticDataByHours',
      'GroupByManagers',
      'ResellerProfitStatistics',
      //'SellTbHitsGrouped',
      //'AliveOnsDayGroup',
      //'Alive30OnsDayGroup',
    ];

    if (!$this->excludeHandlers) {
      return $handlers;
    }

    $exclude = explode(',', $this->excludeHandlers);


    $filtered = array_filter($handlers, function ($handler) use ($exclude) {
      return !in_array($handler, $exclude, true);
    });

    return $filtered;
  }

  /**
   * @inheritdoc
   */
  public function options($actionID)
  {
    return array_merge(
      parent::options($actionID),
      ['launchBuyout', 'isProfilerEnabled', 'ignoreDateRangeLimit', 'excludeHandlers', 'allowBalanceByUserAndDate']
    );
  }

  /**
   * @param $name
   * @return string
   */
  private function getHandlerClass($name)
  {
    return 'mcms\statistic\components\cron\handlers\\' . $name;
  }

  /**
   * Получить из кэша дату следующего запуска пересчета статы по возможным старым постбекам
   * @return string
   */
  private function getRecalcFromCache()
  {
    return Yii::$app->cache->get(self::CACHE_KEY_STATISTIC_RECALC_DATE);
  }

  /**
   * Учтановить в кэш дату следующего запуска пересчета статы по возможным старым постбекам
   * @return string
   */
  private function setRecalcStatDateTommorrow()
  {
    return Yii::$app->cache->set(
      self::CACHE_KEY_STATISTIC_RECALC_DATE,
      date('Y-m-d', strtotime('+1day')),
      self::CACHE_KEY_STATISTIC_RECALC_DATE_DURATION
    );
  }

}
