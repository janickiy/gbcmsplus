<?php
namespace mcms\statistic\components\clear;

use mcms\common\output\ConsoleOutput;
use mcms\common\output\OutputInterface;
use mcms\common\RunnableInterface;
use Yii;
use yii\base\Object;
use yii\db\Connection;

/**
 * Class AbstractCleaner
 * @package mcms\statistic\components\clear
 */
class AbstractCleaner extends Object implements RunnableInterface
{

  /** @var  OutputInterface */
  private $_logger;

  /** @var  Connection */
  protected $db;

  public function init()
  {
    $this->setLogger(new ConsoleOutput()); // логгер по-умолчанию в консоль
    $this->db = Yii::$app->db;
    parent::init();
  }


  public function run(){}

  /**
   * @param OutputInterface $logger
   * @return $this
   */
  public function setLogger(OutputInterface $logger)
  {
    $this->_logger = $logger;
    return $this;
  }

  /**
   * @param $message
   */
  protected function log($message)
  {
    $this->_logger->log(date('H:i:s') . ': ' . $message . "\n\n");
  }
}