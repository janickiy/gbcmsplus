<?php

namespace mcms\statistic\components\newStat\mysql\groupFormats;

use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\mysql\BaseGroupValuesFormatter;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Форматтер для операторов
 */
class Operators extends BaseGroupValuesFormatter
{
  /** @var string шаблон текста */
  public $template = '{name} ({country_name})';
  /**
   * @var int[] Накапливаем тут заранее все значения при конструкторе, чтобы потом вытащить одним запросом.
   */
  private static $bufferValues = [];
  /**
   * @var array статик кэш
   */
  private static $cached = [];

  /**
   * Operators constructor.
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

  /**
   * @inheritdoc
   */
  public function getFormattedPlainValue()
  {
    if (array_key_exists($this->value, self::$cached)) {
      return $this->replaceTemplate(self::$cached[$this->value]);
    }

    self::cacheBufferedValues();

    return $this->replaceTemplate(ArrayHelper::getValue(self::$cached, $this->value));
  }


  private static function cacheBufferedValues()
  {
    $names = (new Query())
      ->select(['name' => 'o.name', 'country_name' => 'c.name', 'id' => 'o.id'])
      ->from('operators o')
      ->leftJoin('countries c', 'c.id = o.country_id')
      ->andWhere(['o.id' => self::$bufferValues])
      ->indexBy('id')
      ->all();

    foreach (self::$bufferValues as $id) {
      self::$cached[$id] = ArrayHelper::getValue($names, $id);
    }
  }

  /**
   * Подменяем шаблон реальными значениями
   * @param $info
   * @return null|string
   */
  private function replaceTemplate($info)
  {
    if (!$info) {
      return null;
    }

    return strtr($this->template, ['{id}' => $this->value, '{name}' => $info['name'], '{country_name}' => $info['country_name']]);
  }
}
