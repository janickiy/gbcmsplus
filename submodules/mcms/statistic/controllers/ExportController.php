<?php

namespace mcms\statistic\controllers;
use mcms\statistic\components\ResellerStatisticExporter;
use Yii;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;


/**
 * Class ExportController
 * @package mcms\statistic\controllers

 * todo Удалить этот класс
 */
class ExportController extends AbstractStatisticController
{

  /**
   * Скачать заготовленный отчет реселлера. По-умолчанию если не передавать date, отдаёт вчерашний отчет.
   * Если не передать параметр month он будет проигнорирован
   * @param null $date Формат: Y-m-d
   * @param string|null $month Формат: Y-m Если указать, то $date использоваться не будет
   * @return mixed
   * @throws yii\web\NotFoundHttpException
   */
  public function actionReseller($date = null, $month = null)
  {
    throw new HttpException(404, 'Sorry. This page was disabled');
    $date = $date ?: Yii::$app->formatter->asDate('-1 day', 'php:Y-m-d');
    $date = $month ? $month : $date;
    $fileName = ResellerStatisticExporter::FILE_PREFIX . $date . '.xlsx';

    $file = Yii::getAlias(ResellerStatisticExporter::FOLDER . $fileName);

    if (!file_exists($file)) {
      throw new NotFoundHttpException('The requested page does not exist.');
    }

    return Yii::$app->response->sendFile($file, null, ['inline' => true]);
  }

  /**
   * Скачать заготовленный файл со статой по ТБ. По-умолчанию если не передавать date, отдаёт вчерашний отчет.
   * @param null $date
   * @return mixed
   * @throws yii\web\NotFoundHttpException
   */
  public function actionTb($date = null)
  {
    $date = $date ?: Yii::$app->formatter->asDate('-1 day', 'php:Y-m-d');
    $fileName = ResellerStatisticExporter::FILE_TB_PREFIX . $date . '.csv';

    $file = Yii::getAlias(ResellerStatisticExporter::FOLDER . $fileName);

    if (!file_exists($file)) {
      throw new NotFoundHttpException('The requested page does not exist.');
    }

    return Yii::$app->response->sendFile($file, null, ['inline' => true]);
  }

}