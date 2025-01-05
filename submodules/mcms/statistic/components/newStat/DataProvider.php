<?php

namespace mcms\statistic\components\newStat;

use mcms\statistic\components\newStat\mysql\Row;
use yii\data\ArrayDataProvider;

/**
 * Провайдер для основной статы.
 */
class DataProvider extends ArrayDataProvider
{
  /** @var Row */
  public $sumRow;
  /** @var Row */
  public $avgRow;
  public $modelClass;

  public function init()
  {
    /*
     * TRICKY:
     * т.к. yii берёт первую модель из провайдера и из неё вытаскивает attributeLabel.
     * А для пустого провайдера такое сделать у него не получается и достать инфу больше неоткуда,
     * поэтому подставляем в modelClass название нашего класса
     * иначе если стата пустая не подгружаются переводы столбцов
     */
    $this->modelClass = $this->modelClass ?: Row::class;
    parent::init();
  }
}
