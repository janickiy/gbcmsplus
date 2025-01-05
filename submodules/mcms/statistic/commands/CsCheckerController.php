<?php

namespace mcms\statistic\commands;

use mcms\common\traits\LogTrait;
use mcms\statistic\components\columnstore\BaseChecker;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Проверяем что лежит в ColumnStore. Сравниваем с innoDb.
 * И ещё смотрим есть ли в ColumnStore повторяющиеся строки, ато ведь там нету PK или уникальных ключей
 */
class CsCheckerController extends Controller
{
  use LogTrait;

  public $dateFrom;
  public $dateTo;

  /**
   * @inheritdoc
   */
  public function options($actionID)
  {
    return ArrayHelper::merge(parent::options($actionID), [
      'dateFrom', 'dateTo',
    ]);
  }

  public function actionIndex()
  {
    if (!$this->validateRequest()) {
      return;
    }

    $dateFrom = Yii::$app->formatter->asDate($this->dateFrom, 'php:Y-m-d');
    $dateTo = Yii::$app->formatter->asDate($this->dateTo, 'php:Y-m-d');

    $this->stdout(date('H:i:s') . " dateFrom=$dateFrom; dateTo=$dateTo" . PHP_EOL);

    foreach (['hits', 'subs', 'rebills', 'offs', 'onetimes', 'solds'] as $checkerKey) {
      $this->stdout(date('H:i:s') . " [[{$checkerKey}]]", Console::FG_YELLOW);

      /** @var BaseChecker $checker */
      $checker = Yii::createObject([
        'class' => '\mcms\statistic\components\columnstore\checkers\\' . ucfirst($checkerKey),
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
      ]);

      $this->stdout(' innodb=', Console::FG_GREY);

      $innoDbCount = $checker->getInnoDbCount();
      $this->stdout($innoDbCount);

      $this->stdout(' columnstore=', Console::FG_GREY);
      $columnstoreCount = $checker->getColumnStoreCount();
      $this->stdout($columnstoreCount, $columnstoreCount === $innoDbCount ? Console::RESET : Console::FG_RED);

      if ($columnstoreCount !== $innoDbCount) {
        Yii::error("$checkerKey кол-во не совпадает. innodb=$innoDbCount;columnstore=$columnstoreCount;dateFrom=$dateFrom;dateTo=$dateTo");
      }

      $this->stdout(' duplicates=', Console::FG_GREY);
      $dupCount = $checker->getColumnStoreDuplicatesCount();
      $this->stdout($dupCount, $dupCount ? Console::FG_RED : Console::RESET);

      if ($dupCount) {
        Yii::error("$checkerKey кол-во дублей в таблице в columnstore=$dupCount. dateFrom=$dateFrom;dateTo=$dateTo");
      }

      $this->stdout(PHP_EOL);
    }
    $this->stdout(date('H:i:s') . ' DONE' . PHP_EOL);
  }

  /**
   * @return bool
   */
  private function validateRequest()
  {
    $hasErrors = false;
    if (!$this->dateFrom) {
      $this->stdout('Необходимо указать --dateFrom' . PHP_EOL, Console::FG_RED);
      $this->stdout(
        '   можно в таких форматах: 2018-09-19|today|-1day и т.д.' . PHP_EOL .
        '   поле обрабатывается через Formatter::asDate($value, \'php:Y-m-d\')' . PHP_EOL,
        Console::FG_GREY
      );
      $hasErrors = true;
    }

    if (!$this->dateTo) {
      $this->stdout('Необходимо указать --dateTo' . PHP_EOL, Console::FG_RED);
      $this->stdout(
        '   можно в таких форматах: 2018-09-19|today|-1day и т.д.' . PHP_EOL .
        '   поле обрабатывается через Formatter::asDate($value, \'php:Y-m-d\')' . PHP_EOL,
        Console::FG_GREY
      );
      $hasErrors = true;
    }

    return !$hasErrors;
  }
}
