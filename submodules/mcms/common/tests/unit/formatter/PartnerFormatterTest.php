<?php
namespace mcms\common\tests\formatter;

use mcms\partners\components\PartnerFormatter;
use Yii;
use mcms\common\codeception\TestCase;

/**
 */
class PartnerFormatterTest extends TestCase
{
  /** @var PartnerFormatter для краткости */
  protected $f;

  protected function setUp()
  {
    parent::setUp();

    $this->f = Yii::createObject([
      'class' => PartnerFormatter::class,
      'dateFormat' => 'php:d.m.Y',
      'datetimeFormat' => 'php:d.m.Y H:i:s',
      'timeFormat' => 'php:H:i:s',
      'thousandSeparator' => ' ',
      'decimalSeparator' => ',',
      'defaultTimeZone' => 'Europe/Moscow',
    ]);
  }

  public function testAsDecimal()
  {
    expect('незначительная точность округляется в ноль', $this->f->asDecimal(0.0004))->same('0');
    expect($this->f->asDecimal(null))->same('0');
    expect($this->f->asDecimal(0))->same('0');
    expect($this->f->asDecimal(0.220))->same('0,22');
    expect($this->f->asDecimal(0.20))->same('0,2');
    expect($this->f->asDecimal(20.0))->same('20');
    expect($this->f->asDecimal(2000.0))->same('2 000');
    expect($this->f->asDecimal(20000000.0))->same('20 000 000');
  }

  public function testAsLandingPrice()
  {
    expect($this->f->asLandingPrice(null))->same('0,00');
    expect($this->f->asLandingPrice(0))->same('0,00');
    expect($this->f->asLandingPrice(0.2241))->same('0,224');
    expect($this->f->asLandingPrice(0.2249))->same('0,225');
    expect($this->f->asLandingPrice(0.223))->same('0,223');
    expect($this->f->asLandingPrice(0.220))->same('0,22');
    expect($this->f->asLandingPrice(0.2))->same('0,20');
    expect($this->f->asLandingPrice(2000.0))->same('2 000,00');
    expect($this->f->asLandingPrice(20000000.0))->same('20 000 000,00');
    expect($this->f->asLandingPrice(20000000.0, 'rub'))->same('20 000 000,00 ₽');
    expect('несуществующая валюта без иконки', $this->f->asLandingPrice(200.0, 'битков'))->same('200,00 битков');
  }

  public function testAsStatisticSum()
  {
    expect($this->f->asLandingPrice(null))->same('0,00');
    expect($this->f->asStatisticSum(null))->same('0,00');
    expect($this->f->asStatisticSum(0))->same('0,00');
    expect($this->f->asStatisticSum(0.2241))->same('0,224');
    expect($this->f->asStatisticSum(0.2249))->same('0,225');
    expect($this->f->asStatisticSum(0.223))->same('0,223');
    expect($this->f->asStatisticSum(0.220))->same('0,22');
    expect($this->f->asStatisticSum(0.2))->same('0,20');
    expect($this->f->asStatisticSum(2000.0))->same('2 000,00');
    expect($this->f->asStatisticSum(20000000.0))->same('20 000 000,00');
  }

}