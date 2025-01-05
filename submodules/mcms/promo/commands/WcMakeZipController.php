<?php


namespace mcms\promo\commands;

use mcms\promo\components\WCHelper;
use yii\console\Controller;
use yii\helpers\Console;
use Yii;

/**
 * Class WcMakeZipController
 * @package mcms\promo\commands
 */
class WcMakeZipController extends Controller
{

  public function actionIndex()
  {
    $this->stdout('Wc zip archive create begin...' . PHP_EOL, Console::FG_GREEN);

    WCHelper::generateZip();

    $this->stdout(sprintf('File %s successfully created%s',
      WCHelper::getZipFilePath(),
      PHP_EOL
    ), Console::FG_GREEN);
  }

  /**
   * Для запуска из миграции например.
   * WcMakeZipController::selfLaunch()
   */
  public static function selfLaunch()
  {
    (new self('wc-make-zip', Yii::$app->getModule('promo')))->actionIndex();
  }
}
