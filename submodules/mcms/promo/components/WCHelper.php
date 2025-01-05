<?php

namespace mcms\promo\components;

use mcms\promo\models\Domain;
use yii\helpers\FileHelper;
use ZipArchive;
use Yii;

/**
 * Class WCHelper
 * @package mcms\promo\components
 */
class WCHelper
{

  const ZIP_PATH = '@uploadPath/promo/';

  const WC_FILE = '@mcms/promo/components/scripts/WC.php';
  const WC_CHECK_FILE = '@mcms/promo/components/scripts/wc_check.php';

  const WC_FILE_NEW_NAME = 'WC.php';
  const WC_CHECK_FILE_NEW_NAME = 'check.php';

  const API_PATH = '/api/?r=wc_check';

  public static function generateZip()
  {
    $folderPath = Yii::getAlias(self::ZIP_PATH);
    if (!file_exists($folderPath)) {
      FileHelper::createDirectory($folderPath);
    }

    $zipPath = self::getZipFilePath();
    if (file_exists($zipPath)) {
      unlink($zipPath);
    }

    $zip = new ZipArchive();
    $zip->open($zipPath, ZIPARCHIVE::CREATE);

    $zip->addEmptyDir('wc');
    $zip->addEmptyDir('wc/cache');

    $zip->addFile(Yii::getAlias(self::WC_FILE), 'wc/'. self::WC_FILE_NEW_NAME);

    $wcCheckContent = file_get_contents(Yii::getAlias(self::WC_CHECK_FILE));

    $wcCheckContent = str_replace('{{API_URL}}', self::getApiUrl(), $wcCheckContent);

    $zip->addFromString ('wc/'. self::WC_CHECK_FILE_NEW_NAME, $wcCheckContent);

    if (file_exists($zipPath)) {
      unlink($zipPath);
    }

    $zip->close();
  }


  /**
   * @return string
   */
  public static function getApiUrl()
  {
    $domain = Domain::findOne(['is_system' => 1, 'status' => Domain::STATUS_ACTIVE]);

    return $domain ? rtrim($domain->url, '/') . self::API_PATH : '';
  }


  /**
   * @return string
   */
  public static function getZipFilePath()
  {
    $zipFileName = Yii::$app->getModule('partners')->api('getProjectName')->getWcZipFileName();
    return Yii::getAlias(self::ZIP_PATH) . $zipFileName;;
  }
}