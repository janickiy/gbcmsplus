<?php

namespace mcms\partners\components\widgets;

use mcms\common\helpers\Html;
use Yii;
use yii\base\Widget;

/**
 * @deprecated используй Yii::$app->formatter->asCurrency() или Yii::$app->formatter->asLandingPrice() или Yii::$app->formatter->asMagicDecimalsPrice() или Yii::$app->formatter->asStatisticSum()
 */
class PriceWidget extends Widget
{

  const CURRENCY_RUB = 'rub';
  const CURRENCY_EUR = 'eur';
  const CURRENCY_USD = 'usd';

  public $currency;
  public $value;
  public $small = false;

  /**
   * @inheritDoc
   */
  public function init()
  {
    $this->value = $this->value !== null ? Yii::$app->formatter->asMagicDecimalsPrice($this->value) : null;
    parent::init();
  }


  /**
   * @inheritDoc
   */
  public function run()
  {
    return ($this->value ? $this->value . ' ' : '') . Html::icon($this->currency);
  }
}
