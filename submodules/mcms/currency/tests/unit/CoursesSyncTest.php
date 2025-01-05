<?php

namespace mcms\currency\tests\unit;

use mcms\common\codeception\TestCase;
use mcms\currency\components\events\CustomCourseBecameUnprofitable;
use mcms\currency\models\Currency;
use Yii;

/**
 * Class CoursesSyncTest
 * @package mcms\currency\tests\unit
 */
class CoursesSyncTest extends TestCase
{
  const CURRENCY = 'czk';
  const TABLE = 'currencies';

  protected $counterEventTriggered = 0;

  protected function _before()
  {
    parent::_before();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 0')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE currencies')->execute();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 1')->execute();
    // Чешская крона. Курсы: to_rub: 3.00, to_usd: 0.04, to_eur: 0.03.
    $sql = file_get_contents(__DIR__ . '/../_data/currencies.sql');
    Yii::$app->db->createCommand($sql)->execute();

    $this->counterEventTriggered = 0;
    // Чтобы не навешивалось несколько раз сперва снимаем
    Yii::$app->off(CustomCourseBecameUnprofitable::class);
    Yii::$app->on(CustomCourseBecameUnprofitable::class, function ($event) {
      ++$this->counterEventTriggered;
    });
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

  // кастомный курс не заполнен (алерта нет)
  public function testEmptyCustomCourse()
  {
    $this->setCustomCourses(null, null, null);

    $currency = Currency::findOne(['code' => self::CURRENCY]);
    $currency->setScenario(Currency::SCENARIO_SYNC);

    $currency->to_rub = 1;
    $currency->to_usd = 2;
    $currency->to_eur = 3;

    $currency->save();

    expect('Событие не должно срабатывать. Кастомный курс не задан', $this->counterEventTriggered)->same(0);
  }

  // кастомный курс > оригинальный курс * процент, оригинальный курс изменился (алерт есть)
  public function testCourseChanged()
  {
    $this->setCustomCourses(1.1, 3, 2);

    $currency = Currency::findOne(['code' => self::CURRENCY]);
    $currency->setScenario(Currency::SCENARIO_SYNC);

    $currency->to_rub = 1;
    $currency->to_usd = 2;
    $currency->to_eur = 3;

    $currency->save();

    expect('Событие должно сработать 2 раза', $this->counterEventTriggered)->same(2);
  }

  // кастомный курс > оригинальный курс * процент, оригинальный курс не изменился (алерта нет)
  public function testCourseNotChanged()
  {
    $this->setCustomCourses(1.1, 3, 2);

    $currency = Currency::findOne(['code' => self::CURRENCY]);
    $currency->setScenario(Currency::SCENARIO_SYNC);

    $currency->to_rub = 3;
    $currency->to_usd = 0.04;
    $currency->to_eur = 0.03;

    $currency->save();

    expect('Событие не должно срабатывать. Курс не изменился', $this->counterEventTriggered)->same(0);
  }
}
