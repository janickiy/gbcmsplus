<?php

namespace mcms\statistic\components\mainStat\mysql\groupFormats;

use mcms\statistic\components\mainStat\FormModel;
use mcms\statistic\components\mainStat\mysql\BaseGroupValuesFormatter;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use mcms\common\helpers\Html;

/**
 * Форматтер для пользователей
 */
class Users extends BaseGroupValuesFormatter
{
  /** @var string шаблон текста */
  public $template = '#{id}. {name}';
  /**
   * @var int[] Накапливаем тут заранее все значения при конструкторе, чтобы потом вытащить одним запросом.
   */
  private static $bufferNames = [];
  /**
   * @var array статик кэш
   */
  private static $cachedNames = [];
  /**
   * Users constructor.
   * @param $value
   * @param FormModel $formModel
   */
  public function __construct($value, FormModel $formModel)
  {
    parent::__construct($value, $formModel);
    self::$bufferNames[] = $this->value;
  }

  /**
   * @inheritdoc
   */
  public function getFormattedValue()
  {
    if (array_key_exists($this->value, self::$cachedNames)) {
      return $this->makeLink(
        Yii::$app->formatter->asStringOrNull($this->replaceTemplate(self::$cachedNames[$this->value]))
      );
    }

    self::cacheBufferedValues();

    return Yii::$app->formatter->asStringOrNull(
      $this->makeLink($this->replaceTemplate(ArrayHelper::getValue(self::$cachedNames, $this->value)))
    );
  }

  private static function cacheBufferedValues()
  {
    $names = (new Query())
      ->select('username')
      ->from('users')
      ->andWhere(['id' => self::$bufferNames])
      ->indexBy('id')
      ->column();

    foreach (self::$bufferNames as $id) {
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
  private function makeLink($title)
  {
    $link = Yii::$app->getModule('users')->api('getOneUser', [
      'user_id' => $this->value
    ])->getUrlParam();

    return Html::a($title, $link, ['data-pjax' => 0, 'target' => '_blank'], [], false);
  }
}
