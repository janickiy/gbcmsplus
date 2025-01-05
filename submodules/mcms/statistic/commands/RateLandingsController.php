<?php

namespace mcms\statistic\commands;


use mcms\statistic\components\rate\RateLandingsHandler;
use Yii;
use yii\console\Controller;

/**
 * Class RateLandingsController
 * @package mcms\statistic\commands
 */
class RateLandingsController extends Controller
{
  /**
   * @var string|int
   */
  public $dateFrom = '-3 days';

  /**
   * @var bool
   */
  public $withLog = false;

  /**
   * @var int
   */
  public $sourceId;

  /**
   * Returns the names of valid options for the action (id)
   * An option requires the existence of a public member variable whose
   * name is the option name.
   * Child classes may override this method to specify possible options.
   *
   * Note that the values setting via options are not available
   * until [[beforeAction()]] is being called.
   *
   * @param string $actionID the action id of the current request
   * @return array the names of the options valid for the action
   */
  public function options($actionId)
  {
    return array_merge(parent::options($actionId), [
      'dateFrom', 'withLog', 'sourceId',
    ]);
  }

  /**
   *
   */
  public function actionIndex()
  {
    (new RateLandingsHandler([
      'dateFrom' => $this->dateFrom,
      'withLog' => $this->withLog,
      'sourceId' => $this->sourceId,
    ]))->run();
  }
}