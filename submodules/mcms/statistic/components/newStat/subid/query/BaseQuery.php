<?php

namespace mcms\statistic\components\newStat\subid\query;

use mcms\statistic\components\newStat\FormModel;
use yii\base\InvalidParamException;
use yii\db\Query;
use Yii;

/**
 * Базовый класс для запросов в БД с группировкой по subid
 */
abstract class BaseQuery  extends Query
{
  /** @var int */
  protected $userId;

  protected $formModel;
  private static $_subidSchemaName;

  /**
   * @param FormModel $formModel
   * @param array $config
   */
  public function __construct(FormModel $formModel, array $config = [])
  {
    $this->formModel = $formModel;
    $this->userId = (int)reset($this->formModel->users);
    if (!$this->userId) {
      throw new InvalidParamException('Parameter userId not found');
    }
    parent::__construct($config);
  }


  /**
   * @return string
   * @throws \yii\db\Exception
   */
  public function getSubidSchemaName()
  {
    if (self::$_subidSchemaName) {
      return self::$_subidSchemaName;
    }

    self::$_subidSchemaName = Yii::$app->sdb->createCommand('SELECT DATABASE()')->queryScalar();

    return self::$_subidSchemaName;
  }

  /**
   * группировка по subid1
   */
  abstract public function handleGroupBySubid1();
  /**
   * группировка по subid1
   */
  abstract public function handleGroupBySubid2();

  /**
   * фильтрация по subid1
   * @param string|string[] $values
   */
  abstract public function handleFilterBySubid1($values);
  /**
   * фильтрация по subid1
   * @param string|string[] $values
   */
  abstract public function handleFilterBySubid2($values);
}
