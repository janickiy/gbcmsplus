<?php

namespace mcms\statistic\components\mainStat;

use mcms\statistic\components\mainStat\mysql\Row;
use yii\data\ArrayDataProvider;

/**
 * Провайдер для основной статы.
 */
class DataProvider extends ArrayDataProvider implements DataProviderInterface
{
  /** @var Row|\mcms\partners\components\mainStat\Row */
  private $_footerRow;
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
    $this->modelClass = Row::class;
    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function getFooterRow()
  {
    return $this->_footerRow;
  }

  /**
   * @inheritdoc
   */
  public function setFooterRow($row)
  {
    $this->_footerRow = $row;
  }
}
