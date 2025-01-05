<?php
namespace mcms\promo\commands;

use mcms\promo\components\landing_sets\LandingSetsLandsUpdater;
use yii\console\Controller;

/**
 * Class LandingSetsLandsController
 * @package mcms\promo\commands
 */
class LandingSetsLandsController extends Controller
{
  public function actionIndex()
  {
    (new LandingSetsLandsUpdater())->run();
  }
}