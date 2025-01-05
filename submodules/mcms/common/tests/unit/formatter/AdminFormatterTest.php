<?php
namespace mcms\common\tests\formatter;

use mcms\common\AdminFormatter;
use Yii;
use mcms\common\codeception\TestCase;

/**
 */
class AdminFormatterTest extends TestCase
{
  /** @var AdminFormatter для краткости */
  protected $f;

  protected function setUp()
  {
    parent::setUp();

    $this->f = Yii::createObject([
      'class' => AdminFormatter::class,
      'dateFormat' => 'php:d.m.Y',
      'datetimeFormat' => 'php:d.m.Y H:i:s',
      'timeFormat' => 'php:H:i:s',
      'thousandSeparator' => ' ',
      'decimalSeparator' => ',',
      'defaultTimeZone' => 'Europe/Moscow',
    ]);
  }


  public function testAsProtectedString()
  {
    $str = '79204335467';

    expect($this->f->asProtectedString($str))->same('79✳✳✳✳✳✳467');
    expect($this->f->asProtectedPhone($str))->same('7920433XXXX');
    expect($this->f->asProtectedString($str, null , null, 'X'))->same('79XXXXXX467');
    expect($this->f->asProtectedString($str, 2 , 2, 'X'))->same('79XXXXXXX67');
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

}