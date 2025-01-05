<?php

namespace mcms\statistic\commands;

use mcms\statistic\components\clear\BannerShowsClear;
use mcms\statistic\components\clear\HitsRename;
use mcms\statistic\components\clear\HitsIntermediate;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Class ClearRedundantDataController
 * @package mcms\statistic\commands
 */
class ClearRedundantDataController extends Controller
{
  /** @var int Сколько дней храним хиты по ТБ */
  public $daysKeepTb;
  /** @var int Сколько дней храним остальные хиты */
  public $daysKeepHits;
  /** @var int Сколько дней храним логи */
  public $daysKeepLogs;
  /** @var int Сколько дней храним постбеки */
  public $daysKeepPostbacks;

  public function options($actionID)
  {
    return ['daysKeepTb', 'daysKeepHits', 'daysKeepLogs', 'daysKeepPostbacks'];
  }

  public function actionHitsIntermediate()
  {
    $this->stdout("~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*\n\n", Console::FG_RED);
    $this->stdout("ВНИМАНИЕ!!! перед запуском остановите кроны по хитам!\n\n", Console::FG_RED);
    $this->stdout("РЕКОМЕНДУЕТСЯ СВЕРИТЬ CREATE TABLE hits и hit_params в скрипте с базой\n\n", Console::FG_PURPLE);
    $this->stdout("РЕКОМЕНДУЕТСЯ ЗАПУСКАТЬ СКРИПТ В screen\n\n", Console::FG_PURPLE);
    $this->stdout("~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*\n", Console::FG_RED);

    if (!$this->confirm('Будет создана промежуточная таблица с новыми актуальными данными по хитам. Продолжить?')) {
      $this->stdout("\nscript interrupted\n");
      return;
    }

    $handler = new HitsIntermediate();
    $handler->run();
  }

  public function actionHitsRename()
  {
    $this->stdout("~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*\n\n", Console::FG_RED);
    $this->stdout("ВНИМАНИЕ!!! перед запуском остановите кроны по хитам!\n\n", Console::FG_RED);
    $this->stdout("РЕКОМЕНДУЕТСЯ СВЕРИТЬ CREATE TABLE hits и hit_params в скрипте с базой\n\n", Console::FG_PURPLE);
    $this->stdout("РЕКОМЕНДУЕТСЯ ЗАПУСКАТЬ СКРИПТ В screen\n\n", Console::FG_PURPLE);
    $this->stdout("~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*\n", Console::FG_RED);

    if (!$this->confirm('Таблица созданная в скрипте hits-intermediate будет переименована в оригинальную и дозаписана накопившимися данными. Продолжить?')) {
      $this->stdout("\nscript interrupted\n");
      return;
    }

    $this->stdout("ДАЛЕЕ НУЖНО ВВЕСТИ ВРЕМЯ С КОТОРОГО ХИТЫ МОГЛИ ИЗМЕНИТСЯ ПОКА ВЫПОЛНЯЛСЯ СКРИПТ hits-intermediate\n", Console::FG_YELLOW);
    $this->stdout("Например:\n", Console::FG_YELLOW);
    $this->stdout(" - вы запустили скрипт hits-intermediate в 12:00\n", Console::FG_YELLOW);
    $this->stdout(" - скрипт отработал за 5 часов.\n", Console::FG_YELLOW);
    $this->stdout(" - хиты могли быть изменены в базе за время переливки в течении часа\n", Console::FG_YELLOW);
    $this->stdout("Соответственно нужно задать время 11:00 в формате UNIX TIMESTAMP\n", Console::FG_YELLOW);
    $this->stdout("Таким образом скрипт обновит все хиты между 11:00 и 17:00 и дозапишет новые.\n", Console::FG_YELLOW);
    $from = $this->prompt('Введите время в формате UNIX TIMESTAMP, с момента которого стоит обновить данные в промежуточной таблице: ');
    if ($from < time() - 86400 * 7) {
      $this->stdout("\nПожалуйста, сгенерируйте промежуточную таблицу заново, так как вы пытаетесь обновить данные, которые были созданы неделю или более назад\n");
      return;
    }
    $handler = new HitsRename(['updateFrom' => $from]);
    $handler->run();
  }

  public function actionBannerShows()
  {
    if (!$this->confirm('Очистить показы баннеров banner_shows? (скрипт долгий)')) {
      $this->stdout("\nscript interrupted\n");
      return;
    }

    $handler = new BannerShowsClear();
    $handler->run();
  }

  /**
   * Чистка хитов/хит-парамс, логов, постбеков и меток
   */
  public function actionClearAll()
  {
    $this->stdout("~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*\n\n", Console::FG_RED);
    $this->stdout("ВНИМАНИЕ!!! перед запуском остановите кроны по хитам, постбекам и меткам!\n\n", Console::FG_RED);
    $this->stdout("РЕКОМЕНДУЕТСЯ СВЕРИТЬ CREATE TABLE hits и hit_params в скрипте с базой\n\n", Console::FG_PURPLE);
    $this->stdout("РЕКОМЕНДУЕТСЯ СВЕРИТЬ CREATE TABLE postbacks в скрипте с базой\n\n", Console::FG_PURPLE);
    $this->stdout("РЕКОМЕНДУЕТСЯ ЗАПУСКАТЬ СКРИПТ В screen\n\n", Console::FG_PURPLE);
    $this->stdout("~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*\n", Console::FG_RED);

    // Хиты
    $from = time();
    $params = [];
    if ($this->daysKeepHits) {
      $params['daysKeepHits'] = $this->daysKeepHits;
    }
    if ($this->daysKeepTb) {
      $params['daysKeepTb'] = $this->daysKeepTb;
    }
    (new HitsIntermediate($params))->run();
    (new HitsRename(['updateFrom' => $from]))->run();
  }
}
