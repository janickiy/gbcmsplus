<?php

namespace mcms\statistic\components\subid;

use common\components\ClickhouseHelper;
use mcms\common\RunnableInterface;
use mcms\common\traits\LogTrait;
use Yii;
use yii\base\Object;

/**
 * Базовый хэндлер
 */
abstract class BaseHandler extends Object implements RunnableInterface
{
  use LogTrait;

  /** @var  RegularUpdateConfig */
  public $cfg;

  private static $_mainSchemaName;
  private static $_sdbSchemaName;

  /** @var array $trialOperators id орпеаторов именющих trial */
  private $_trialOperators;

  /**
   * @return string
   * @throws \yii\db\Exception
   */
  public function getMainSchemaName()
  {
    if (self::$_mainSchemaName) {
      return self::$_mainSchemaName;
    }

    self::$_mainSchemaName = Yii::$app->db->createCommand('SELECT DATABASE()')->queryScalar();

    return self::$_mainSchemaName;
  }

  /**
   * @return string
   * @throws \yii\db\Exception
   */
  public function getSdbSchemaName()
  {
    if (self::$_sdbSchemaName) {
      return self::$_sdbSchemaName;
    }

    self::$_sdbSchemaName = Yii::$app->sdb->createCommand('SELECT DATABASE()')->queryScalar();

    return self::$_sdbSchemaName;
  }

  /**
   * @return string
   */
  public static function getName()
  {
    $path = explode('\\', static::class);
    return array_pop($path);
  }

  /**
   * Условие по trial операторам
   * @param string $operatorField поле для которого применить условие
   * @param bool $not NOT IN
   * @return string
   */
  public function getTrialOperatorsInCondition($operatorField, $not = false)
  {
    $this->_trialOperators = Yii::$app->getModule('promo')->api('trialOperators')->getResult();
    if (empty($this->_trialOperators)) {
      return '1 = 1';
    }
    return $operatorField . ($not ? ' NOT' : '') . ' IN (' . implode(', ', $this->_trialOperators) . ')';
  }

  protected function getClickhouseMysqlConnectionString($tableName)
  {
    return ClickhouseHelper::getClickhouseMysqlConnectionString($tableName);
  }
}
