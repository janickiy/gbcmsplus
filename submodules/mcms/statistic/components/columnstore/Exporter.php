<?php

namespace mcms\statistic\components\columnstore;

use mcms\common\RunnableInterface;
use mcms\common\traits\LogTrait;
use const SORT_ASC;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Query;

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
class Exporter implements RunnableInterface
{

  use LogTrait;

  /** @var ExporterConfig */
  protected $cfg;

  /**
   * @param ExporterConfig $config
   */
  public function __construct(ExporterConfig $config)
  {
    $this->cfg = $config;
  }

  /**
   * @return bool
   * @throws InvalidConfigException
   */
  public function run()
  {
    if (!$this->cfg->dir) {
      throw new InvalidConfigException('dir param required');
    }

    $innerQuery = null;

    foreach ($this->cfg->getWithList() as $queryKey) {
      $q = $this->getQuery($queryKey);

      if ($innerQuery === null) {
        $innerQuery = $q;
        continue;
      }
      $innerQuery->union($q, true);
    }

    $outerQuery = (new Query())->from(['inner' => $innerQuery])->orderBy(['time' => SORT_ASC]);

    $file = $this->cfg->getDir() . "/facts/{$this->cfg->dateFrom}_{$this->cfg->dateTo}.csv";

    $result = Yii::$app->db
      ->createCommand(
        $outerQuery->createCommand()->rawSql
        . " INTO OUTFILE '{$file}'
          FIELDS TERMINATED BY ','
          ENCLOSED BY '\"'
          LINES TERMINATED BY '\n';"
      )
      ->execute();

    $this->log(strtr('exported=:result ' . PHP_EOL, [
      ':result' => $result,
    ]));

    return true;
  }

  /**
   * @param $type
   * @throws InvalidConfigException
   * @return BaseQuery|object
   */
  private function getQuery($type)
  {
    /** @var BaseQuery $query */
    return Yii::createObject(
      '\mcms\statistic\components\columnstore\queries\\' . ucfirst($type),
      [$this->cfg]
    );
  }
}
