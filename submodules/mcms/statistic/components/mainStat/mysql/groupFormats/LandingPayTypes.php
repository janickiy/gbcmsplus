<?php

namespace mcms\statistic\components\mainStat\mysql\groupFormats;

use mcms\statistic\components\mainStat\FormModel;
use mcms\statistic\components\mainStat\mysql\BaseGroupValuesFormatter;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Форматтер для типов оплаты
 */
class LandingPayTypes extends BaseGroupValuesFormatter
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
   * LandingPayTypes constructor.
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
    if (array_key_exists($this->value, self::$cachedNames)) {
      return Yii::$app->formatter->asStringOrNull($this->replaceTemplate(self::$cachedNames[$this->value]));
    }

    self::cacheBufferedValues();

    return Yii::$app->formatter->asStringOrNull(
      $this->replaceTemplate(ArrayHelper::getValue(self::$cachedNames, $this->value))
    );
  }

  private static function cacheBufferedValues()
  {
    $names = (new Query())
      ->select('name')
      ->from('landing_pay_types')
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
}