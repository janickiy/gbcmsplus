<?php

use mcms\common\translate\SystemLanguage;

$yii2path = __DIR__ . '/../../../vendor/yiisoft/yii2';
require($yii2path . '/BaseYii.php');

/**
 * Class Yii
 * заменяем класс /vendor/yiisoft/yii2/Yii.php для возможности расширения
 */
class Yii extends \yii\BaseYii
{

  /**
   * @var ApplicationDocs|\yii\console\Application|\yii\web\Application the application instance
   */
  public static $app;

  /**
   * Method _t - translate messages \Yii::_t
   * @param $langString
   * @param array $params
   * @param null $language
   * @return string
   */
  public static function _t($langString, $params = [], $language = null)
  {
    if (SystemLanguage::getDebugMode() === SystemLanguage::DEBUG_MODE_SOURCE) return $langString;

    $category = $langString;
    $message = $langString;
    if (($pos = strrpos($langString, '.')) !== false) {
      $message = substr($langString, $pos + 1);
      $category = substr($langString, 0, $pos);
    }

    return parent::t($category, $message, $params, $language);

  }

}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = require($yii2path . '/classes.php');
Yii::$container = new yii\di\Container();

/** Components documentation
 * @property mcms\payments\components\paysystem_icons\PaysystemIcons $paysystemIcons
 * @property \mcms\common\mgmp\MgmpClient $mgmpClient
 * @property rgk\utils\components\grid\export\GridExporter $gridExporter
 * @property rgk\queue\QueueFacade $queue
 * @property \mcms\common\AdminFormatter $formatter
 * @property \rgk\settings\components\SettingsBuilder $settingsBuilder
 * @property \rgk\settings\components\SettingsManager $settingsManager
 * @property rgk\exchange\components\Currencies $exchange
 * @property \yii\db\Connection $dbCs
 * @property \yii\db\Connection $sdb
 */
abstract class ApplicationDocs
{
}