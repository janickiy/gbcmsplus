<?php


namespace mcms\promo\commands;

use Yii;
use yii\console\Controller;
use mcms\promo\components\WebmasterNewLandsHandler;

/**
 * Class WebmasterNewLandsController
 * @package mcms\promo\commands
 */
class WebmasterNewLandsController extends Controller
{

  public function actionIndex()
  {
    (new WebmasterNewLandsHandler())->run();
  }

}