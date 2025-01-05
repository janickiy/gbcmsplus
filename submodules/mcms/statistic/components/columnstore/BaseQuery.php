<?php


namespace mcms\statistic\components\columnstore;

use mcms\common\traits\LogTrait;
use yii\db\Query;

/**
 * Базовый Query
 */
abstract class BaseQuery extends Query
{

  use LogTrait;

  /** @var ExporterConfig */
  protected $cfg;

  /**
   * @param ExporterConfig $config
   * @param $params
   */
  public function __construct(ExporterConfig $config, $params = [])
  {
    $this->cfg = $config;
    parent::__construct($params);
  }
}
