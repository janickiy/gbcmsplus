<?php

namespace mcms\statistic\components;

use PDO;
use Yii;
use yii\db\Query;

/**
 * Class ExportMenuCsv
 * @package mcms\statistic\components
 */
class ExportMenuCsv
{
  /**
   * Экспортирут csv
   * @param string $filename название файла которое будет экспортировано
   * @param array $requestData requestData для формирования модели статистики
   * @param string $statisticType тип модели статистики (см. \mcms\statistic\Module::getStatisticModel)
   * @param array $selectedAttrs массив где ключи - выбранные атрибуты для экспорта (атрибуты модели), значения - функция форматирования
   */
  public static function export($filename, $requestData, $statisticType, $selectedAttrs)
  {
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'";');

    /**
     * @var \mcms\statistic\components\AbstractDetailStatistic $statisticModel
     */
    $statisticModel = Yii::$app->getModule('statistic')->getStatisticModel($requestData, $statisticType)->getStatisticModel();
    $attributeLabels = $statisticModel->getExportAttributeLabels();

    /**
     * @var Query $query
     */
    list($query,) = $statisticModel->getStatisticGroupQueries();

    // Open file
    $fileHandle = fopen('php://output', 'w');

    $delimiter = ';';
    $endl = "\r\n";
    $enclosure = '"';
    fwrite($fileHandle, "\xEF\xBB\xBF");	//	Enforce UTF-8 BOM Header

    $elements = [];
    foreach ($selectedAttrs as $element => $format) {
      if (isset($attributeLabels[$element])) {
        $elements[] = str_replace($enclosure, $enclosure . $enclosure, $attributeLabels[$element]);
      } else {
        $elements[] = $element;
      }
    }
    fwrite($fileHandle, implode($delimiter, $elements) . $endl);

    $limit = Yii::$app->getModule('statistic')->getExportLimit();
    Yii::$app->db->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    foreach ($query->batch() as $rows) {
      if ($limit < 0) break;
      foreach ($rows as $row) {
        $limit --;
        if ($limit < 0) break;
        $elements = [];
        foreach ($selectedAttrs as $element => $format) {
          $value = $row[$element];
          if ($format) {
            $value = $format($value, $row);
          }
          $elements[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $value) . $enclosure;
        }

        fwrite($fileHandle, implode($delimiter, $elements) . $endl);
      }
    }
    Yii::$app->db->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

    // Close file
    fclose($fileHandle);
  }
}