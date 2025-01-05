<?php

namespace mcms\statistic\commands;

use mcms\statistic\components\ResellerProfitsChecker;
use mcms\statistic\components\ResellerProfits;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Заполнение Статистики в Расчете реселлера
 * Пример:
 * php yii statistic/reseller-profits --dateFrom=2017-09-04
 *
 * todo жирный получился контроллер. Надо выносить логику и покрывать её юнитами.
 *
 * Class ResellerProfitsController
 * @package mcms\payments\commands
 */
class ResellerProfitsController extends Controller
{
  public $dateFrom;
  public $dateTo;
  /**
   * @var bool По-умолчанию скрипт обновит только те холды, у стран которых изменилось правило расхолда.
   * Но если данную настройку сделать true, то обновятся независимо от того именилось правило расхолда или нет.
   * Обычно это необходимо если в расчете изменилась какая-то логика и надо пересчитать на проде.
   */
  public $forceUpdateHolds = false;
  /**
   * @inheritdoc
   */
  public function options($actionID)
  {
    return array_merge(
      parent::options($actionID),
      ['dateFrom', 'dateTo', 'forceUpdateHolds']
    );
  }

  /**
   * @throws \yii\web\NotFoundHttpException
   * @throws \yii\db\Exception
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\base\Exception
   * @throws \yii\base\InvalidParamException
   */
  public function actionIndex()
  {
    $resellerProfits = new ResellerProfits;
    $resellerProfits->dateFrom = $this->dateFrom;
    $resellerProfits->dateTo = $this->dateTo;
    $resellerProfits->forceUpdateHolds = $this->forceUpdateHolds;
    $resellerProfits->logger = function ($message, $breakAfter = true, $breakBefore = false) {
      parent::stdout(($breakBefore ? PHP_EOL : '') . $message . ($breakAfter ? PHP_EOL : ''), Console::FG_GREEN);
    };

    $resellerProfits->execute();
  }

  public function actionCheck()
  {
    $resellerProfits = new ResellerProfitsChecker;
    if ($this->dateFrom) $resellerProfits->dateFrom = $this->dateFrom;
    $resellerProfits->logger = function ($message) {
      parent::stdout($message, Console::FG_GREEN);
    };
    $resellerProfits->execute();
  }
}