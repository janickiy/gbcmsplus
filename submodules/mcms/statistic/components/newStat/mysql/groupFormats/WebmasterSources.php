<?php

namespace mcms\statistic\components\newStat\mysql\groupFormats;

use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\mysql\BaseGroupValuesFormatter;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Форматтер для источников вебмастеров
 */
class WebmasterSources extends BaseGroupValuesFormatter
{
  /** @var string шаблон текста */
  public $template = '#{id}. {name}';
  /**
   * @var int[] Накапливаем тут заранее все значения при конструкторе, чтобы потом вытащить одним запросом.
   */
  private static $bufferValues = [];
  /**
   * @var array статик кэш
   */
  private static $cachedNames = [];

  /**
   * WebmasterSources constructor.
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
    return Yii::$app->formatter->asStringOrNull($this->makeLink($this->getFormattedPlainValue()));
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

  private static function cacheBufferedValues()
  {
    $names = (new Query())
      ->select('name')
      ->from('sources')
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
   * делаем ссылку
   * @param $title
   * @return string
   */
  protected function makeLink($title)
  {
    $link = Yii::$app->getModule('promo')->api('sourceById', [
      'source_id' => $this->value
    ])->getUrlParam();

    return Html::a($title, $link, ['data-pjax' => 0, 'target' => '_blank']);
  }
}
