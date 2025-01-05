<?php

namespace mcms\statistic\commands;

use mcms\common\traits\LogTrait;
use mcms\statistic\components\columnstore\ExporterConfig;
use mcms\statistic\components\columnstore\Exporter;
use mcms\statistic\models\cs\Fact;
use Yii;
use yii\console\Controller;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Выгружаем стату для MariaDb ColumnStore
 * @see Exporter
 */
class CsExportController extends Controller
{
  use LogTrait;

  /**
   * @var string папка для выгрузки
   */
  public $dir;
  /**
   * @var string
   * Что экспортировать (через запятую)
   * Возможные варианты: all|hits|subs|rebills|offs|onetimes|solds
   * По-умолчанию all (запускает все экспорты).
   */
  public $with;
  /**
   * @var int С какого id экспортировать включительно.
   * Если не указано - берём макс. значение + 1 из ColumnStore
   */
  public $hitsFrom;
  /**
   * @var int До какого id экспортировать включительно.
   * Если не указано - берём макс. значение из InnoDb
   */
  public $hitsTo;
  /**
   * @var int С какого id экспортировать включительно.
   * Если не указано - берём макс. значение + 1 из ColumnStore
   */
  public $subsFrom;
  /**
   * @var int До какого id экспортировать включительно.
   * Если не указано - берём макс. значение из InnoDb
   */
  public $subsTo;
  /**
   * @var int С какого id экспортировать включительно.
   * Если не указано - берём макс. значение + 1 из ColumnStore
   */
  public $rebillsFrom;
  /**
   * @var int До какого id экспортировать включительно.
   * Если не указано - берём макс. значение из InnoDb
   */
  public $rebillsTo;
  /**
   * @var int С какого id экспортировать включительно.
   * Если не указано - берём макс. значение + 1 из ColumnStore
   */
  public $offsFrom;
  /**
   * @var int До какого id экспортировать включительно.
   * Если не указано - берём макс. значение из InnoDb
   */
  public $offsTo;
  /**
   * @var int С какого id экспортировать включительно.
   * Если не указано - берём макс. значение + 1 из ColumnStore
   */
  public $onetimesFrom;
  /**
   * @var int До какого id экспортировать включительно.
   * Если не указано - берём макс. значение из InnoDb
   */
  public $onetimesTo;
  /**
   * @var int С какого id экспортировать включительно.
   * Если не указано - берём макс. значение + 1 из ColumnStore
   */
  public $soldsFrom;
  /**
   * @var int До какого id экспортировать включительно.
   * Если не указано - берём макс. значение из InnoDb
   */
  public $soldsTo;
  /**
   * @var string (можно в формате php или типа того: "-2day")
   * Если не указано, то присвоится "yesterday"
   */
  public $dateFrom;
  /**
   * @var string (можно в формате php или типа того: "-2day")
   * Если не указано, то "today"
   */
  public $dateTo;
  /**
   * @var int С какого id экспортировать включительно.
   * Если не указано - берём макс. значение + 1 из ColumnStore
   */
  public $complaintsFrom;
  /**
   * @var int До какого id экспортировать включительно.
   * Если не указано - берём макс. значение из InnoDb
   */
  public $complaintsTo;
  /**
   * @var int С какого id экспортировать включительно.
   * Если не указано - берём макс. значение + 1 из ColumnStore
   */
  public $refundsFrom;
  /**
   * @var int До какого id экспортировать включительно.
   * Если не указано - берём макс. значение из InnoDb
   */
  public $refundsTo;


  /**
   * @inheritdoc
   */
  public function options($actionID)
  {
    return ArrayHelper::merge(parent::options($actionID), [
      'dir', 'with', 'interactive',
      'hitsFrom', 'hitsTo',
      'subsFrom', 'subsTo',
      'offsFrom', 'offsTo',
      'rebillsFrom', 'rebillsTo',
      'onetimesFrom', 'onetimeTo',
      'soldsFrom', 'soldsTo',
      'complaintsFrom', 'complaintsTo',
      'refundsFrom', 'refundsTo',
      'dateFrom', 'dateTo',
    ]);
  }


  /**
   * Выгружаем стату для MariaDb ColumnStore
   *
   * Есть возможность запускать как регулярно кроном, так и вручную,
   * указав нужный диапазон ID-шников [[hitsFrom]], [[hitsTo]], [[subsFrom]] и т.д.
   *
   * Файлы CSV кладутся в папку на сервере с БД обычного мускуля.
   * На сервере ColumnStore админы расшарили эту папку между двумя машинами, чтобы загружать эти CSV уже в ColumnStore.
   *
   * Чтобы избежать проблем типа: приложение недогенерило файл, а CS уже начал его импорт. Решили сделать lock-файл.
   * Файл создается в той же папке рядом с CSV по завершению экспорта.
   * Экспорт не начнется повторно, пока lock-файл не будет удален со стороны CS.
   * Точно так же импорт со стороны CS не начнется, пока lock-файл не будет создан.
   */
  public function actionIndex()
  {
    if (!$this->dir) {
      $this->stdout(
        'Укажи папку для экспорта, например для локалки --dir=/home/mcms-columnstore/data' . PHP_EOL,
        Console::FG_RED
      );
      Yii::$app->end();
    }

    $configParams = [
      'dir' => $this->dir,
      'with' => $this->with,
      'hitsTo' => $this->hitsTo,
      'subsTo' => $this->subsTo,
      'offsTo' => $this->offsTo,
      'rebillsTo' => $this->rebillsTo,
      'onetimesTo' => $this->onetimesTo,
      'soldsTo' => $this->soldsTo,
      'complaintsTo' => $this->complaintsTo,
      'refundsTo' => $this->refundsTo,
    ];

    $configParams['dateFrom'] = $this->getDateFrom();
    $configParams['dateTo'] = $this->getDateTo();
    $configParams['hitsFrom'] = $this->getHitsFrom($configParams);
    $configParams['subsFrom'] = $this->getSubsFrom($configParams);
    $configParams['offsFrom'] = $this->getOffsFrom($configParams);
    $configParams['rebillsFrom'] = $this->getRebillsFrom($configParams);
    $configParams['onetimesFrom'] = $this->getOnetimesFrom($configParams);
    $configParams['soldsFrom'] = $this->getSoldsFrom($configParams);
    $configParams['complaintsFrom'] = $this->getComplaintsFrom($configParams);
    $configParams['refundsFrom'] = $this->getRefundsFrom($configParams);

    $config = $this->createConfig($configParams);

    $this->log('CONFIG ' . print_r($config->toArray(), true));

    if ($this->interactive && !Console::confirm('Config is OK?', true)) {
      $this->log('Cancelled by user.' . PHP_EOL);
      return;
    }

    if ($this->lockFileIsExists($config)) {
      $this->log("lock-файл ещё не удален, новый экспорт не выполняем. Путь: {$config->getLockFilePath()}" . PHP_EOL);
      return;
    }

    for ($date = $config->dateFrom; $date <= $config->dateTo; $date = Yii::$app->formatter->asDate($date . ' +1day', 'php:Y-m-d')) {
      $this->log('[' . Yii::$app->formatter->asDate('now', 'php:H:i:s') . '] date=' . $date . '...' . PHP_EOL);

      $innerConfig = $this->createConfig(ArrayHelper::merge($configParams, [
        'dateFrom' => $date,
        'dateTo' => $date
      ]));

      /** @var Exporter $exporter */
      $exporter = Yii::createObject(Exporter::class, [$innerConfig]);
      $exporter->run();
    }

    $this->createLockFile($config);

    $this->log('done' . PHP_EOL, [Console::FG_GREEN]);
  }

  /**
   * @param $configParams
   * @return object|ExporterConfig
   * @throws \yii\base\InvalidConfigException
   */
  protected function createConfig($configParams)
  {
    return Yii::createObject(ExporterConfig::class, [[
      'dir' => $configParams['dir'],
      'with' => $configParams['with'],
      'hitsFrom' => $configParams['hitsFrom'],
      'hitsTo' => $configParams['hitsTo'],
      'subsFrom' => $configParams['subsFrom'],
      'subsTo' => $configParams['subsTo'],
      'offsFrom' => $configParams['offsFrom'],
      'offsTo' => $configParams['offsTo'],
      'rebillsFrom' => $configParams['rebillsFrom'],
      'rebillsTo' => $configParams['rebillsTo'],
      'onetimesFrom' => $configParams['onetimesFrom'],
      'onetimesTo' => $configParams['onetimesTo'],
      'soldsFrom' => $configParams['soldsFrom'],
      'soldsTo' => $configParams['soldsTo'],
      'complaintsFrom' => $configParams['complaintsFrom'],
      'complaintsTo' => $configParams['complaintsTo'],
      'refundsFrom' => $configParams['refundsFrom'],
      'refundsTo' => $configParams['refundsTo'],
      'dateFrom' => $configParams['dateFrom'],
      'dateTo' => $configParams['dateTo'],
    ]]);
  }

  /**
   * @return string
   * @throws \yii\base\InvalidConfigException
   */
  private function getDateFrom()
  {
    return Yii::$app->formatter->asDate($this->dateFrom ?: 'yesterday', 'php:Y-m-d');
  }

  /**
   * @return string
   * @throws \yii\base\InvalidConfigException
   */
  private function getDateTo()
  {
    return Yii::$app->formatter->asDate($this->dateTo ?: 'today', 'php:Y-m-d');
  }

  /**
   * @param $configParams
   * @return int|null
   */
  private function getHitsFrom($configParams)
  {
    if ($this->hitsFrom) {
      return $this->hitsFrom;
    }

    $maxIdFromCs = $this->getMaxFromCs(Fact::TYPE_HIT, $configParams['dateFrom'], $configParams['dateTo']);

    if (!$maxIdFromCs) {
      return null;
    }

    $this->log("hitsFrom не указан. Заменяем на следующий за макс. hitId из ColumnStore={$maxIdFromCs}" . PHP_EOL);
    return $maxIdFromCs + 1;
  }

  /**
   * @param $configParams
   * @return int|null
   */
  private function getSubsFrom($configParams)
  {
    if ($this->subsFrom) {
      return $this->subsFrom;
    }

    $maxIdFromCs = $this->getMaxFromCs(Fact::TYPE_SUB, $configParams['dateFrom'], $configParams['dateTo']);

    if (!$maxIdFromCs) {
      return null;
    }

    $this->log("subsFrom не указан. Заменяем на следующий за макс. hitId из ColumnStore={$maxIdFromCs}" . PHP_EOL);
    return $maxIdFromCs + 1;
  }

  /**
   * @param $configParams
   * @return int|null
   */
  private function getOffsFrom($configParams)
  {
    if ($this->offsFrom) {
      return $this->offsFrom;
    }

    $maxIdFromCs = $this->getMaxFromCs(Fact::TYPE_OFF, $configParams['dateFrom'], $configParams['dateTo']);

    if (!$maxIdFromCs) {
      return null;
    }

    $this->log("offsFrom не указан. Заменяем на следующий за макс. hitId из ColumnStore={$maxIdFromCs}" . PHP_EOL);
    return $maxIdFromCs + 1;
  }

  /**
   * @param $configParams
   * @return int|null
   */
  private function getRebillsFrom($configParams)
  {
    if ($this->rebillsFrom) {
      return $this->rebillsFrom;
    }

    $maxIdFromCs = $this->getMaxFromCs(Fact::TYPE_REBILL, $configParams['dateFrom'], $configParams['dateTo']);

    if (!$maxIdFromCs) {
      return null;
    }

    $this->log("rebillsFrom не указан. Заменяем на следующий за макс. hitId из ColumnStore={$maxIdFromCs}" . PHP_EOL);
    return $maxIdFromCs + 1;
  }

  /**
   * @param $configParams
   * @return int|null
   */
  private function getOnetimesFrom($configParams)
  {
    if ($this->onetimesFrom) {
      return $this->onetimesFrom;
    }

    $maxIdFromCs = $this->getMaxFromCs(Fact::TYPE_ONETIME, $configParams['dateFrom'], $configParams['dateTo']);

    if (!$maxIdFromCs) {
      return null;
    }

    $this->log("onetimesFrom не указан. Заменяем на следующий за макс. hitId из ColumnStore={$maxIdFromCs}" . PHP_EOL);
    return $maxIdFromCs + 1;
  }

  /**
   * @param $configParams
   * @return int|null
   */
  private function getSoldsFrom($configParams)
  {
    if ($this->soldsFrom) {
      return $this->soldsFrom;
    }

    $maxIdFromCs = $this->getMaxFromCs(Fact::TYPE_SOLD, $configParams['dateFrom'], $configParams['dateTo']);

    if (!$maxIdFromCs) {
      return null;
    }

    $this->log("soldsFrom не указан. Заменяем на следующий за макс. hitId из ColumnStore={$maxIdFromCs}" . PHP_EOL);
    return $maxIdFromCs + 1;
  }

  /**
   * @param $configParams
   * @return int|null
   */
  private function getComplaintsFrom($configParams)
  {
    if ($this->complaintsFrom) {
      return $this->complaintsFrom;
    }

    $maxIdFromCs = $this->getMaxFromCs(array_values(Fact::getComplaintTypes()), $configParams['dateFrom'], $configParams['dateTo']);

    if (!$maxIdFromCs) {
      return null;
    }

    $this->log("complaintsFrom не указан. Заменяем на следующий за макс. hitId из ColumnStore={$maxIdFromCs}" . PHP_EOL);
    return $maxIdFromCs + 1;
  }

  /**
   * @param $configParams
   * @return int|null
   */
  private function getRefundsFrom($configParams)
  {
    if ($this->refundsFrom) {
      return $this->refundsFrom;
    }

    $maxIdFromCs = $this->getMaxFromCs(array_values(Fact::getRefundTypes()), $configParams['dateFrom'], $configParams['dateTo']);

    if (!$maxIdFromCs) {
      return null;
    }

    $this->log("refundsFrom не указан. Заменяем на следующий за макс. hitId из ColumnStore={$maxIdFromCs}" . PHP_EOL);
    return $maxIdFromCs + 1;
  }

  /**
   * Получить максимальное значение ID, которое есть в CS
   * @param $type int|int[] Exporter::TYPE_HIT, Exporter::TYPE_SUB и т.д.
   * @param $dateFrom
   * @param $dateTo
   * @return false|null|string
   */
  private function getMaxFromCs($type, $dateFrom, $dateTo)
  {
    return (new Query)
      ->select('MAX(id)')
      ->from('facts')
      ->andWhere(['type' => $type])
      ->andFilterWhere(['>=', 'date', $dateFrom])
      ->andFilterWhere(['<=', 'date', $dateTo])
      ->scalar(Yii::$app->dbCs);
  }

  /**
   * Существует ли лок-файл на сервере БД
   * @param ExporterConfig $config
   * @return bool
   */
  private function lockFileIsExists(ExporterConfig $config)
  {
    /**
     * TRICKY есть проблема с тем, что расшаренная папка между сервером mariaDb и columnStore недоступна из приложения
     * для этого используем хак, читая файл и кладя в таблицу logs (неважно вообще какая таблица, т.к. 1я строка игнорится
     * в любом случае). Если выбрасывается исключение, что файла такого нет - значит его нет :).
     * А если успешно выполняется команда, то значит файл прочитался успешно.
     */
    try {
      Yii::$app->db->createCommand("
        LOAD DATA INFILE '{$config->getLockFilePath()}'
        INTO TABLE logs
        FIELDS TERMINATED BY ','
        ENCLOSED BY '\"'
        LINES TERMINATED BY '\n'
        IGNORE 1 ROWS;
      ")->execute();
      return true;
    } catch (\Exception $e) {
      return false;
    }
  }

  /**
   * Создаем лок-файл
   * @param ExporterConfig $config
   * @throws \yii\db\Exception
   */
  private function createLockFile(ExporterConfig $config)
  {
    Yii::$app->db->createCommand("
      SELECT null
      INTO OUTFILE '{$config->getLockFilePath()}'
      FIELDS TERMINATED BY ','
      ENCLOSED BY '\"'
      LINES TERMINATED BY '\n';
      ")->execute();
  }
}
