<?php

namespace mcms\pages\commands;

use mcms\pages\components\widgets\PagesWidget;
use mcms\pages\models\Category;
use mcms\pages\models\CategoryProp;
use mcms\pages\models\Page;
use mcms\pages\models\PageProp;
use Yii;
use yii\caching\TagDependency;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;

class InitLpController extends Controller
{
  public $landing;

  /** @var bool $forceInit запускать без подтверждения */
  protected $forceInit = 0;

  public function options($actionID)
  {
    return ['forceInit'];
  }

  public function actionIndex($landing)
  {
    if (!$this->forceInit && !$this->confirm("generate landing $landing ?")) {
      return Controller::EXIT_CODE_NORMAL;
    }
    $this->landing = $landing;

    $this->stdout($this->landing . " BEGIN >> \n");

    $this->migrateSql();

    $this->replaceFiles();
    $this->replacePropFiles();

    $this->stdout("LANDING MIGRATED\n", Console::FG_GREEN);
  }

  private function replaceFiles()
  {
    $this->stdout("replaceFiles begin\n");
    $dstPath = Yii::getAlias('@uploadPath/pages/pages/gallery');

    FileHelper::createDirectory($dstPath);

    $dirSrc = __DIR__ . '/page_files/' . $this->landing;
    if (!is_dir($dirSrc)) return false;
    
    $dir = opendir($dirSrc);
    while(false !== ( $file = readdir($dir)) ) {
      if (( $file == '.' ) || ( $file == '..' )) continue;
      copy($dirSrc . '/' . $file, $dstPath . '/' . $file);
    }
    closedir($dir);
    $this->stdout("replaceFiles end\n");
  }

  private function migrateSql()
  {
    $this->stdout("migrateSql begin\n");
    Page::deleteAll();
    Category::deleteAll();

    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 0')->execute();

    $dirSrc = __DIR__ . '/sql/' . $this->landing;
    $dir = opendir($dirSrc);
    while(false !== ( $file = readdir($dir)) ) {
      if (( $file == '.' ) || ( $file == '..' )) continue;

      $sql = file_get_contents($dirSrc . '/' . $file);
      Yii::$app->db->createCommand($sql)->execute();
    }
    closedir($dir);

    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 1')->execute();

    TagDependency::invalidate(Yii::$app->cache, [
      PagesWidget::CACHE_TAG,
      CategoryProp::BY_CODE_CACHE_TAG
    ]);

    $this->stdout("migrateSql end\n");
  }

  private function replacePropFiles()
  {
    $this->stdout("replacePropFiles begin\n");
    $dstPath = Yii::getAlias('@uploadPath/pages/' . PageProp::FILE_FOLDER);

    FileHelper::createDirectory($dstPath);

    $dirSrc = __DIR__ . '/page_prop_files/' . $this->landing;
    $dir = opendir($dirSrc);
    while(false !== ( $categoryCode = readdir($dir)) ) {
      if (( $categoryCode == '.' ) || ( $categoryCode == '..' )) continue;

      $category = Category::find()->where(['code' => $categoryCode])->one();
      if (!$category) continue;


      $categoryDirSrc = $dirSrc . '/' . $categoryCode;

      $categoryDir = opendir($categoryDirSrc);
      while(false !== ( $categoryPropCode = readdir($categoryDir)) ) {
        if (( $categoryPropCode == '.' ) || ( $categoryPropCode == '..' )) continue;

        $categoryProp = CategoryProp::find()->where([
          'code' => $categoryPropCode,
          'page_category_id' => $category->id
        ])->one();
        if (!$categoryProp) continue;


        $categoryPropDirSrc = $categoryDirSrc . '/' . $categoryPropCode;
        $categoryPropDir = opendir($categoryPropDirSrc);

        while(false !== ( $file = readdir($categoryPropDir)) ) {
          if (($file == '.') || ($file == '..')) continue;

          $dstFileDir = $dstPath . '/' . $categoryProp->id;

          FileHelper::createDirectory($dstFileDir);

          copy($categoryPropDirSrc . '/' . $file, $dstFileDir . '/' . $file);
        }

        closedir($categoryPropDir);
      }
      closedir($categoryDir);
    }
    closedir($dir);
    $this->stdout("replacePropFiles end\n");
  }
}