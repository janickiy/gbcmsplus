<?php

namespace mcms\common\traits;

use mcms\common\output\ConsoleOutput;
use mcms\common\output\OutputInterface;
use Yii;
use yii\helpers\Console;

/**
 * Class LogTrait
 * @package mcms\common\traits
 */
trait LogTrait
{
  /** @var  OutputInterface */
  private $logger;

  /**
   * @return ConsoleOutput|OutputInterface|object
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\di\NotInstantiableException
   */
  private function getLogger()
  {
    if ($this->logger) return $this->logger;

    $this->logger = Yii::$container->has(OutputInterface::class)
      ? Yii::$container->get(OutputInterface::class)
      : new ConsoleOutput()
    ;

    return $this->logger;
  }

  /**
   * @param $string
   * @param array $params
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\di\NotInstantiableException
   */
  public function log($string, $params = [])
  {
    $this->getLogger()->log($string, $params);
  }
}
