<?php

namespace mcms\statistic\tests\commands;

use mcms\statistic\components\newStat\ExportMenu;
use mcms\common\helpers\ArrayHelper;
use mcms\statistic\components\helpers\CsvHelper;
use mcms\statistic\components\newStat\BaseFetch;
use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\Grid;
use mcms\statistic\models\ColumnsTemplateNew as ColumnsTemplate;
use Yii;
use yii\console\Controller;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\FileHelper;
use yii\helpers\Json;

/**
 * Скрипт создание CSV для тестов статы. Запускать через php tests/bin/yii statistic-tests/generate-csv
 * Скрипт загружает в бд фикстуры которые используются в тесте StatisticTest и экспортирует стату в csv по фильтрам и
 * группировкам указанным в скрипте, сравнивает csv файлы с предыдущими и заменяет их. Если есть различия в файлах они
 * выводятся.
 * Тест StatisticTest проходится по выгруженым файлам csv, достает из названий группировку и филтры и выгружает стату в
 * csv и поколоночно сравнивает файлы.
 *
 */
class GenerateCsvController extends Controller
{
  private $_data;
  private $_paramsWithGroups;

  private $csvFolder;
  private $newCsvFolder;
  private $sqlFolder;

  const START_DATE = '2019-02-01';
  const END_DATE = '2019-03-30';
  const CURRENCY_ID = 3;
  const CURRENCY_CODE = 'eur';

  /**
   * @inheritdoc
   */
  public function init()
  {
    if (!YII_ENV_DEV) {
      die('Запуск на проде запрешен!');
    }
    $this->csvFolder = __DIR__ . '/../_data/new_statistic_csv';
    $this->newCsvFolder = __DIR__ . '/../_data/tmp_new_statistic_csv';
    $this->sqlFolder = __DIR__ . '/../_data/new_statistic';

    parent::init();
  }

  public function actionGenerateCsv()
  {
    echo 'Загружаем в бд фикстуры' . PHP_EOL;
    $this->loadFixtures();
    echo PHP_EOL . 'Загрузка фикстур закончена' . PHP_EOL . PHP_EOL;

    echo 'Начинаем создавать файлы с выгрузкой статистики' . PHP_EOL;
    $this->createNewStatFiles();
    echo PHP_EOL . 'Выгрузка статистики закончена' . PHP_EOL . PHP_EOL;

    echo 'Начинаем сравнение' . PHP_EOL;
    $this->compare();
    echo 'Сравнение завершено' . PHP_EOL;

    echo 'Заменяем  csv файлы' . PHP_EOL;
    $this->replaceFiles();
  }

  /**
   * Сравнение файлов
   */
  private function loadFixtures()
  {
    //загрузка в бд фикстур
    $files = array_diff(scandir($this->sqlFolder, 0), ['..', '.']);

    foreach ($files as $file) {
      $sql = file_get_contents($this->sqlFolder . '/' . $file);
      Yii::$app->db->createCommand($sql)->execute();
    }

    if (!file_exists($this->newCsvFolder)) {
      FileHelper::createDirectory($this->newCsvFolder);
    }
  }

  /**
   * Сравнение файлов
   */
  private function compare()
  {
    $files = array_diff(scandir($this->newCsvFolder, 0), ['..', '.']);

    foreach ($files as $file) {
      $oldData = CsvHelper::statisticCsvToArray($this->csvFolder . DIRECTORY_SEPARATOR . $file);
      $newData = CsvHelper::statisticCsvToArray($this->newCsvFolder . DIRECTORY_SEPARATOR . $file);

      $oldFile = $oldData['data'];
      $newFile = $newData['data'];


      $header = $oldData['header'];

      foreach ($oldFile as $group => $oldRow) {
        $newRow = ArrayHelper::getValue($newFile, $group);

        if (!$newRow) {
          echo 'Строка с группировкой "' . $group . '" в файле ' . $file . ' не найдена' . PHP_EOL;
          continue;
        }

        foreach ($oldRow as $column => $oldValue) {
          $columnName = ArrayHelper::getValue($header, $column);
          $newValue = ArrayHelper::getValue($newRow, $column);
          if (!$newRow) {
            echo 'Колонка "' . $columnName . ' не найдена"; группировка "' . $group . '"; файл ' . $file . PHP_EOL;
            continue;
          }

          if ($oldValue !== $newValue) {
            echo 'Колонка "' . $columnName . '"; группировка "' . $group . '"; файл ' . $file . '; отличается значение' . PHP_EOL . PHP_EOL;
            echo 'Значение старое) ' . $oldValue . ', новое) ' . $newValue . PHP_EOL;
          }
        }
      }
    }
  }

  /**
   * Заменяем файлы для тестов статы
   */
  private function replaceFiles()
  {
    $files = array_diff(scandir($this->newCsvFolder, 0), ['..', '.']);
    foreach ($files as $file) {
      unlink($this->csvFolder . DIRECTORY_SEPARATOR . $file);
    }
    rmdir($this->csvFolder);
    rename($this->newCsvFolder, $this->csvFolder);
  }

  /**
   * Создание файлов с экспортом статы
   */
  private function createNewStatFiles()
  {
    $paramsWithGroups = $this->getParamsWithGroups();
    foreach ($paramsWithGroups as $statisticParams) {
      $exportFileName = '';

      foreach (ArrayHelper::getValue($statisticParams, 'FormModel') as $key => $value) {
        // Имя файла содержит все группировки + фильтры
        $value = is_array($value) ? implode(CsvHelper::GROUP_DELIMITER, $value) : $value;
        $exportFileName .= $key . '_' . $value . '__';
      }

      $formModel = new FormModel();

      $formModel->load($statisticParams);

      /** @var BaseFetch $fetch */
      $fetch = Yii::$container->get(BaseFetch::class, [$formModel, ColumnsTemplate::SYS_TEMPLATE_ALL]);

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
        ]
      ]);

      $exportGrid = new Grid([
        'dataProvider' => $exportDataProvider,
        'statisticModel' => $formModel,
        'templateId' => ColumnsTemplate::SYS_TEMPLATE_ALL,
        'isExportOnly' => true,
        'toggleData' => false,
      ]);

      $cols = $exportGrid->getExportColumns();
      $exportRequestParam = 'exportFull_testExport';

      $exportMenu = new ExportMenu(['dataProvider' => $exportDataProvider, 'toggleData' => false]);
      $_POST[$exportRequestParam] = true;
      $_POST[$exportMenu->exportTypeParam] = ExportMenu::FORMAT_CSV;
      $_POST[$exportMenu->colSelFlagParam] = true;
      $_POST[$exportMenu->exportColsParam] = Json::encode(array_keys($cols));

      ExportMenu::widget([
        'options' => ['id' => 'testExport'],
        'clearBuffers' => true,
        'folder' => $this->newCsvFolder,
        'stream' => false,
        'isExportOnly' => true,
        'dataProvider' => $exportDataProvider,
        'isPartners' => true,
        'statisticModel' => $formModel,
        'filename' => $exportFileName,
        'columns' => $cols,
        'toggleData' => false,
        'templateId' => ColumnsTemplate::SYS_TEMPLATE_ALL,
      ]);

      echo '.';
    }
  }

  /**
   * Фильтры статы + группировки
   * @return array
   */
  private function getParamsWithGroups()
  {
    if (!empty($this->_paramsWithGroups)) {
      return $this->_paramsWithGroups;
    }
    $this->_paramsWithGroups = [];
    foreach ($this->getGroups() as $group) {
      foreach ($this->getParams() as $statisticParams) {
        $group = is_array($group) ? $group : [$group];
        $groupData = ['groups' => $group];
        $statisticParams['FormModel'] = array_merge($groupData, $statisticParams['FormModel']);
        $this->_paramsWithGroups[] = $statisticParams;
      }
    }
    return $this->_paramsWithGroups;
  }

  /**
   * Фильтры статы
   * @return array
   */
  private function getParams()
  {
    $data = $this->getData();
    $statisticParams = [
      [
        'FormModel' => [
          'dateFrom' => $data['startDate'],
          'dateTo' => $data['endDate'],
          'currency' => self::CURRENCY_CODE,
        ]
      ],
      [
        'FormModel' => [
          'dateFrom' => $data['startDate'],
          'dateTo' => $data['endDate'],
          'currency' => self::CURRENCY_CODE,
          'landingPayTypes' => $data['landing_pay_type_id'],
        ]
      ],
      [
        'FormModel' => [
          'dateFrom' => $data['startDate'],
          'dateTo' => $data['endDate'],
          'currency' => self::CURRENCY_CODE,
          'providers' => $data['provider_id'],
        ]
      ],
      [
        'FormModel' => [
          'dateFrom' => $data['startDate'],
          'dateTo' => $data['endDate'],
          'currency' => self::CURRENCY_CODE,
          'countries' => $data['country_id'],
        ]
      ],
      [
        'FormModel' => [
          'dateFrom' => $data['startDate'],
          'dateTo' => $data['endDate'],
          'currency' => self::CURRENCY_CODE,
          'operators' => $data['operator_id'],
        ]
      ],
      [
        'FormModel' => [
          'dateFrom' => $data['startDate'],
          'dateTo' => $data['endDate'],
          'currency' => self::CURRENCY_CODE,
          'users' => $data['user_id'],
        ]
      ],
      [
        'FormModel' => [
          'dateFrom' => $data['startDate'],
          'dateTo' => $data['endDate'],
          'currency' => self::CURRENCY_CODE,
          'streams' => $data['stream_id'],
        ]
      ],
      [
        'FormModel' => [
          'dateFrom' => $data['startDate'],
          'dateTo' => $data['endDate'],
          'currency' => self::CURRENCY_CODE,
          'sources' => $data['source_id'],
        ]
      ],
      [
        'FormModel' => [
          'dateFrom' => $data['startDate'],
          'dateTo' => $data['endDate'],
          'currency' => self::CURRENCY_CODE,
          'landings' => $data['landing_id'],
        ]
      ],
      [
        'FormModel' => [
          'dateFrom' => $data['startDate'],
          'dateTo' => $data['endDate'],
          'currency' => self::CURRENCY_CODE,
          'platforms' => $data['platform_id'],
        ],
      ]
    ];

    return $statisticParams;
  }

  /**
   * Получение данных для фильтров
   * @return array
   */
  private function getData()
  {
    if (!empty($this->_data)) {
      return $this->_data;
    }

    $startDate = self::START_DATE;
    $endDate = self::END_DATE;

    $this->_data = (new Query)->select([
      'landing_pay_type_id',
      'provider_id',
      'country_id',
      'operator_id',
      'user_id',
      'stream_id',
      'source_id',
      'landing_id',
      'platform_id',
      'startDate' => new Expression("'$startDate'"),
      'endDate' => new Expression("'$endDate'"),
    ])
      ->from('statistic')
      ->andWhere(['between', 'date', $startDate, $endDate])
      ->andWhere(['currency_id' => self::CURRENCY_ID])
      ->one();

    return $this->_data;
  }

  /**
   * группировки в стате
   * @return array
   */
  private function getGroups()
  {
    return [
      'dates',
      'monthNumbers',
      'weekNumbers',
      'landings',
      'webmasterSources',
      'arbitraryLinks',
      'streams',
      'platforms',
      'operators',
      'countries',
      'providers',
      'users',
      'landingPayTypes',
      'managers',
      ['dates', 'monthNumbers']
    ];
  }

}
