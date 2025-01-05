<?php

namespace mcms\statistic\components\newStat\mysql\groupFormats;

use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\mysql\BaseGroupValuesFormatter;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Форматтер для страны
 */
class Countries extends BaseGroupValuesFormatter
{
  /** @var string шаблон текста */
  public $template = '{name}';
  /**
   * @var int[] Накапливаем тут заранее все значения при конструкторе, чтобы потом вытащить одним запросом.
   */
  private static $bufferValues = [];
  /**
   * @var array статик кэш
   */
  private static $cachedNames = [];

  /**
   * Countries constructor.
   * @param $value
   * @param FormModel $formModel
   */
  public function __construct($value, FormModel $formModel)
  {
    parent::__construct($value, $formModel);
    self::$bufferValues[] = $this->value;
  }

  /**
   * @inheritdoc
   */
  public function getFormattedValue()
  {
    return Yii::$app->formatter->asStringOrNull($this->getFormattedPlainValue());
  }

  private static function cacheBufferedValues()
  {
    $names = (new Query())
      ->select('name')
      ->from('countries')
      ->andWhere(['id' => self::$bufferValues])
      ->indexBy('id')
      ->column();

    foreach (self::$bufferValues as $id) {
      self::$cachedNames[$id] = ArrayHelper::getValue($names, $id);
    }
  }

  /**
   * Подменяем шаблон реальными значениями
   * @param $name
   * @return null|string
   */
  private function replaceTemplate($name)
  {
    if (!$name) {
      return null;
    }

    return strtr($this->template, ['{id}' => $this->value, '{name}' => $name]);
  }

  /**
   * @inheritdoc
   */
  public function getFormattedPlainValue()
  {
    if (array_key_exists($this->value, self::$cachedNames)) {
      return $this->replaceTemplate(self::$cachedNames[$this->value]);
    }

    self::cacheBufferedValues();

    return $this->replaceTemplate(ArrayHelper::getValue(self::$cachedNames, $this->value));
  }
}
