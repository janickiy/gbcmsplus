<?php

namespace mcms\currency\tests\unit;

use mcms\common\codeception\TestCase;
use mcms\currency\components\events\CustomCourseBecameUnprofitable;
use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\currency\models\Currency;
use Yii;
use yii\base\Event;

/**
 * Class GetCoursesTest
 * @package mcms\currency\tests\unit
 */
class GetCoursesTest extends TestCase
{
  const CURRENCY = 'czk';
  const TABLE = 'currencies';

  protected function _before()
  {
    parent::_before();
    // Чешская крона. Курсы: to_rub: 3.00, to_usd: 0.04, to_eur: 0.03.
    // С наложенным процентом: to_rub: 2.94, to_usd: 0.0392, to_eur: 0.0294.
    $sql = file_get_contents(__DIR__ . '/../_data/currencies.sql');
    Yii::$app->db->createCommand($sql)->execute();
  }

  /**
   * Устанавливаем кастомный курс
   * @param $toRub
   * @param $toUsd
   * @param $toEur
   */
  protected function setCustomCourses($toRub, $toUsd, $toEur)
  {
    Yii::$app->db->createCommand()->update(self::TABLE,
      [
        'custom_to_rub' => $toRub,
        'custom_to_usd' => $toUsd,
        'custom_to_eur' => $toEur,
      ],
      [
        'code' => self::CURRENCY
      ])->execute();
  }

  // кастомный курс не заполнен (используется оригинальный + процент)
  public function testEmptyCustomCourse()
  {
    $this->setCustomCourses(null, null, null);

    $currencyObj = PartnerCurrenciesProvider::getInstance()
      ->getCurrencies()
      ->getCurrency(self::CURRENCY);

    expect('Неверный курс рубля', $currencyObj->getToRub())->same(2.94);
    expect('Неверный курс доллара', $currencyObj->getToUsd())->same(0.0392);
    expect('Неверный курс евро', $currencyObj->getToEur())->same(0.0294);
  }

  // кастомный курс > оригинальный курс * процент, оригинальный курс изменился (используется оригинальный + процент)
  public function testCourseChanged()
  {
    $this->setCustomCourses(2.95, 0.0393, 0.0295);

    $currencyObj = PartnerCurrenciesProvider::getInstance()
      ->getCurrencies()
      ->getCurrency(self::CURRENCY);

    expect('Неверный курс рубля', $currencyObj->getToRub())->same(2.94);
    expect('Неверный курс доллара', $currencyObj->getToUsd())->same(0.0392);
    expect('Неверный курс евро', $currencyObj->getToEur())->same(0.0294);
  }

  // кастомный курс <= оригинальный курс * процент (используется кастомный)
  public function _testCourseNotChanged()
  {
    $this->setCustomCourses(2.93, 0.039, 0.029);

    $currencyObj = PartnerCurrenciesProvider::getInstance()
      ->getCurrencies()
      ->getCurrency(self::CURRENCY);

    expect('Неверный курс рубля', $currencyObj->getToRub())->same(2.93);
    expect('Неверный курс доллара', $currencyObj->getToUsd())->same(0.039);
    expect('Неверный курс евро', $currencyObj->getToEur())->same(0.029);
  }
}
