<?php

namespace mcms\statistic\components;


use mcms\common\traits\LogTrait;
use mcms\statistic\models\mysql\ResellerStatistic;
use mcms\statistic\Module;
use Yii;
use yii\base\Object;
use yii\helpers\FileHelper;

/**
 * Компонент длял выгрузки статистики реселлера за диапазон дат.
 * Используется в @see \mcms\statistic\commands\ExportController
 *
 * Class ResellerStatisticExporter
 * @package mcms\statistic\components
 */
class ResellerStatisticExporter extends Object
{
  const FILE_PREFIX = 'reseller_';
  const FILE_TB_PREFIX = 'tb_';
  /**
   * @var string Папка по-умолчанию
   */
  const FOLDER = '@rootPath/protected/uploads/statistic/';
  public $dateFrom;
  public $dateTo;
  /**
   * @var bool Ежемесячная ли выгрузка
   */
  public $isMonthly;


  use LogTrait;

  public function init()
  {
    parent::init();
    FileHelper::createDirectory(Yii::getAlias(self::FOLDER));
  }


  /**
   * Выгрузка в xlsx со статой сделанная с для аффшарка с группировкой по
   * 'date', 'currency', 'countryCode', 'operator',  'user'
   */
  public function export()
  {
    $columns = [];

    if (!$this->isMonthly) {
      $columns[] = 'date';
    }

    $columns = array_merge($columns, [
      'currency',
      'countryCode',
      'operator',
      'user',

      'resHits',
      'resUniques',
      'resTb',
      'resAccepted',
      'resRevAccepted',
      'resSubs',
      'resOffs24',
      'resOffs',
      'resRebills',
      'resRevResSum',
      'resRevPartnerSum',
      'resRebillsOnDate',
      'resProfitOnDate',
      'resOnetimeResSum',
      'resOnetimePartnerSum',
      'resCpaAccepted',
      'resOnetimes',
      'resSold',
      'resComplains',
      'resCalls',
      'resVisibleSubscriptions',

      'iSubs',
      'iRebillsOnDate',
      'iProfitOnDate',
      'iTotalSum',
      'iBuyoutSum',
      'iOffs24',
      'iOffs',
      'iRebills',
    ]);

    $this->log(date('H:i:s') . ': Preparing DataProvider...' . PHP_EOL);

    $statisticModel = new ResellerStatistic([
      'dateFrom' => $this->dateFrom,
      'dateTo' => $this->dateTo,
      'isMonthly' => $this->isMonthly
    ]);

    $dataProvider = $statisticModel->getDataProvider();
    $dataProvider->setPagination(false);
    $this->log(date('H:i:s') . ': Drawing export file...' . PHP_EOL);

    Yii::$app->gridExporter->export([
      'dataProvider' => $dataProvider,
      'columns' => $columns,
      'filename' => self::FILE_PREFIX . ($this->isMonthly ? date('Y-m', strtotime($this->dateTo)) : $this->dateTo),
      'folder' => self::FOLDER,
    ]);
  }

  /**
   * Выгрузка в csv юзера, оператора, метки 2 и количества ТБ с группировкой по юзеру, оператору, метке 2
   * Выгрузка для того чтобы ресы могли собирать информацию по ТБ и улучшать свой траф
   * Сделано для affshark, потому что у них зверский ТБ
   */
  public function exportTb()
  {
    $this->log(date('H:i:s') . ': Preparing data TB...' . PHP_EOL);
    $rows = Yii::$app->db->createCommand('SELECT s.user_id, operator_id, hp.label2, COUNT(h.id) as count_tb FROM hits h 
LEFT JOIN hit_params hp ON hp.hit_id=h.id
LEFT JOIN sources s ON h.source_id=s.id
WHERE h.is_tb>0 AND h.landing_id!=0 AND h.date>=:dateFrom AND h.date<=:dateTo 
GROUP BY user_id, operator_id, hp.label2
', [':dateFrom' => $this->dateFrom, ':dateTo' => $this->dateTo])->queryAll();

    $folder = Yii::getAlias(self::FOLDER);
    $result = 'user_id' . "\t" . 'operator_id' . "\t" . 'label2' . "\t" . 'count_tb' . "\n";
    foreach ($rows as $row) {
      $result .= $row['user_id'] . "\t" . $row['operator_id'] . "\t" . $row['label2'] . "\t" . $row['count_tb'] . "\n";
    }

    $file = $folder . '/' . self::FILE_TB_PREFIX . $this->dateTo . '.csv';
    $this->log(date('H:i:s') . ': Drawing export TB file...' . PHP_EOL);
    file_put_contents($file, $result);
  }


}