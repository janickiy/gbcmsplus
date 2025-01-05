<?php

namespace mcms\statistic\components\newStat\subid;

/**
 */
class Row extends \mcms\statistic\components\newStat\mysql\Row
{

  public function init()
  {
    parent::init();
  }

  /**
   * @return RowDataDto
   */
  public function getRowDataDto()
  {
    if (!$this->_rowDataDto) {
      $this->_rowDataDto = new RowDataDto();
    }
    return $this->_rowDataDto;
  }

  /**
   * @inheritdoc
   */
  public function getToBuyoutHits()
  {
    return $this->getRowDataDto()->toBuyoutHits;
  }

  /**
   * @inheritdoc
   */
  public function getToBuyoutAccepted()
  {
    return $this->getToBuyoutHits() - $this->getRowDataDto()->toBuyoutTb;
  }

  /**
   * @inheritdoc
   */
  public function getToBuyoutUnique()
  {
    return $this->getRowDataDto()->toBuyoutUnique;
  }


}
