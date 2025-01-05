<?php

namespace mcms\statistic\components\helpers;

use yii\helpers\ArrayHelper;

class CsvHelper
{
  /** @const string разделитель группировок */
  const GROUP_DELIMITER = '---';

  /**
   * CSV-файл с экспортированной статистикой превращаем в массив
   * на выходе получаем массив ['data' => массив строк с ключем в виде конкатенации группировок, 'header' => массив названий колонок]
   * @param $filename
   * @param string $delimiter
   * @return array
   */
  public static function statisticCsvToArray($filename, $delimiter = ';')
  {
    // кол-во ячеей в группировке
    $keyLength = 1;
    // Если в имени файла есть символ -, значит группировка двойная
    if (strpos($filename, self::GROUP_DELIMITER) !== false) {
      $keyLength = 2;
    }
    if (!file_exists($filename) || !is_readable($filename)) {
      return [];
    }
    $header = NULL;
    $data = [];
    if (($handle = fopen($filename, 'r')) !== FALSE) {
      $i = 0;
      while (($row = fgetcsv($handle, 10000, $delimiter)) !== FALSE) {
        if (!$header) {
          $header = $row;
        } else {
          $key = ArrayHelper::getValue($row, 0);
          $skip = $i === 0;
          if ($keyLength == 2) {
            $key .= ArrayHelper::getValue($row, 1);
            $skip = $i === 1;
          }
          if ($skip) {
            // Пропуск колонок группировки
            continue;
          }
          $data[$key] = $row;
        }
        $i++;
      }
      fclose($handle);
    }
    return ['data' => $data, 'header' => $header];
  }
}