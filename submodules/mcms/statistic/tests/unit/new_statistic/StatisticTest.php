<?php

namespace mcms\statistic\tests\unit\main_statistic;

use mcms\common\codeception\TestCase;
use mcms\statistic\components\helpers\CsvHelper;
use mcms\statistic\components\newStat\BaseFetch;
use mcms\statistic\components\newStat\ExportMenu;
use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\Grid;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use mcms\statistic\models\ColumnsTemplateNew;
use yii\helpers\Json;

class StatisticTest extends TestCase
{
  private $sqlDir = '/../../_data/new_statistic/';
  private $csvDir = '/../../_data/new_statistic_csv/';
  private $testDir = '/../../../../../../protected/uploads/new_statistic_test/';

  protected function setUp()
  {
    parent::setUp();

    $files = array_diff(scandir(__DIR__ . $this->sqlDir, 0), ['..', '.']);
    foreach ($files as $file) {
      $sql = file_get_contents(__DIR__ . $this->sqlDir . $file);
      Yii::$app->db->createCommand($sql)->execute();
    }

    $this->testDir = __DIR__ .  $this->testDir;

    if (file_exists($this->testDir)) {
      FileHelper::removeDirectory($this->testDir);
    }
    FileHelper::createDirectory($this->testDir);

    $this->loginAsRoot();
  }

  // TODO
  public function testGroup()
  {
    $csvFiles = array_diff(scandir(__DIR__ . $this->csvDir, 0), ['..', '.']);
    foreach ($csvFiles as $csvFile) {

      $params = explode('__', $csvFile);

      //получаем группировку
      $statisticParams = [];
      $group = explode(CsvHelper::GROUP_DELIMITER, str_replace('groups_', '', $params[0]));
      $statisticParams['FormModel']['groups'] = $group;
      //оставляем в массиве только фильтры.
      array_pop($params);
      array_shift($params);

      foreach ($params as $param) {
        $statParam = explode('_', $param);
        $statisticParams['FormModel'][$statParam[0]] = $statParam[1];
      }

      $formModel = new FormModel();

      $formModel->load($statisticParams);

      /** @var BaseFetch $fetch */
      $fetch = Yii::$container->get(BaseFetch::class, [$formModel, ColumnsTemplateNew::SYS_TEMPLATE_ALL]);

      $exportDataProvider = $fetch->getDataProvider([
        'sort' => [
          'attributes' => [
            'group' => [
              'default' => SORT_DESC
            ]
          ],
          'defaultOrder' => [
            'group' => SORT_DESC
          ]
        ],
      ]);

      $exportGrid = new Grid([
        'dataProvider' => $exportDataProvider,
        'statisticModel' => $formModel,
        'templateId' => ColumnsTemplateNew::SYS_TEMPLATE_ALL,
        'isExportOnly' => true,
      ]);

      $cols = $exportGrid->getExportColumns();
      $exportRequestParam = 'exportFull_testExport';

      //TRICKY для полчучения post параметров создаем объект с обязательным датапровайдером
      $exportMenu = new ExportMenu(['dataProvider' => $exportDataProvider]);

      $_POST[$exportRequestParam] = true;
      $_POST[$exportMenu->exportTypeParam] = ExportMenu::FORMAT_CSV;
      $_POST[$exportMenu->colSelFlagParam] = true;
      $_POST[$exportMenu->exportColsParam] = Json::encode(array_keys($cols));

      ExportMenu::widget([
        'options' => ['id' => 'testExport'],
        'clearBuffers' => true,
        'folder' => $this->testDir,
        'stream' => false,
        'isExportOnly' => true,
        'dataProvider' => $exportDataProvider,
        'isPartners' => true,
        'statisticModel' => $formModel,
        'filename' => pathinfo($csvFile, PATHINFO_FILENAME),
        'columns' => $cols,
        'templateId' => ColumnsTemplateNew::SYS_TEMPLATE_ALL,
      ]);

      $newData = CsvHelper::statisticCsvToArray(  $this->testDir . $csvFile);
      $oldData = CsvHelper::statisticCsvToArray(__DIR__ . $this->csvDir . $csvFile);


      $oldFile = $oldData['data'];
      $newFile = $newData['data'];
      $header = $oldData['header'];

      foreach ($oldFile as $group => $oldRow) {

        $newRow = ArrayHelper::getValue($newFile, $group);

        self::assertNotNull($newRow, 'Строка с группировкой "' . $group . '" в файле ' . $csvFile . ' не найдена');
        foreach ($oldRow as $column => $oldValue) {
          $columnName = ArrayHelper::getValue($header, $column);
          $newValue = ArrayHelper::getValue($newRow, $column);
          self::assertNotNull($newRow, 'Колонка "' . $columnName . '" не найдена. Группировка "' . $group
            . '", файл ' . $csvFile);

          self::assertSame($oldValue, $newValue, 'Колонка "' .$column . ' - ' . $columnName . '", группировка "' . $group
            . '" в файле ' . $csvFile . ' различаются');
        }
      }
    }
  }
}