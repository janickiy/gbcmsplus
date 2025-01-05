<?php
namespace mcms\promo\tests\unit\partnerProgram;

use mcms\common\codeception\TestCase;
use mcms\payments\models\UserPaymentSetting;
use mcms\promo\components\PartnerProgramSync;
use mcms\promo\models\PartnerProgramItem;
use mcms\promo\models\PersonalProfit;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class PartnerProgramSyncTest
 * @package mcms\promo\tests\unit\partnerProgram
 */
class PartnerProgramSyncTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.personal_profits_sync',
      'promo.partner_program_items',
      'payments.user_payment_settings',
    ]);
  }

  /**
   * @inheritDoc
   */
  protected function setUp()
  {
    parent::setUp();
    $this->loginById(1);
    // сбросить кеш настроек выплат
    Yii::$app->cache->flush();
  }


  public function testSync()
  {
    $userCurrency = UserPaymentSetting::findOne(['user_id' => 101])->currency;
    /** @var PartnerProgramSync|\PHPUnit_Framework_MockObject_MockObject $stub */
    $stub = $this->getMockBuilder(PartnerProgramSync::class)
      ->setMethods(['getPartnerProgramId'])
      ->setConstructorArgs(['config' => ['userId' => 101]])
      ->getMock();
    $stub->method('getPartnerProgramId')->willReturn(1);
    $stub->init();
    $stub->run();

    $personalProfits = $this->getPartnerProfit(101);
    $partnerProgramItems = $this->getPartnerProgramItems(1);
    $this->assertEquals(
      count($personalProfits),
      count($partnerProgramItems),
      'Количество personalProfits и partnerProgramItems для юзера должно быть одинаковым'
    );
    foreach ($partnerProgramItems as $key => $val) {
      $this->assertArrayHasKey($key, $personalProfits, 'Не все данные партнерской программы');
      $this->assertEquals($val['landing_id'], $personalProfits[$key]['landing_id'], 'Лендинг неверен');
      $this->assertEquals($val['operator_id'], $personalProfits[$key]['operator_id'], 'Оператор неверен' );
      $this->assertEquals($val['rebill_percent'], $personalProfits[$key]['rebill_percent'], 'Процент за ребилл неверен');
      $this->assertEquals($val['buyout_percent'], $personalProfits[$key]['buyout_percent'], 'Процент выкупа неверен');
      $this->assertEquals($val['cpa_profit_' . $userCurrency], $personalProfits[$key]['cpa_profit_' . $userCurrency], 'CPA неверен');
    }
  }

  public function testSyncDry()
  {
    $userId = 104;
    $userCurrency = UserPaymentSetting::findOne(['user_id' => $userId])->currency;
    /** @var PartnerProgramSync|\PHPUnit_Framework_MockObject_MockObject $stub */
    $stub = $this->getMockBuilder(PartnerProgramSync::class)
      ->setMethods(['getPartnerProgramId'])
      ->setConstructorArgs(['config' => ['userId' => $userId]])
      ->getMock();
    $stub->method('getPartnerProgramId')->willReturn(1);
    $stub->init();
    $stub->run();

    $personalProfits = $this->getPartnerProfit($userId);
    $partnerProgramItems = $this->getPartnerProgramItems(1);
    $this->assertEquals(
      count($personalProfits),
      count($partnerProgramItems),
      'Количество personalProfits и partnerProgramItems для юзера должно быть одинаковым'
    );
    foreach ($partnerProgramItems as $key => $val) {
      $this->assertArrayHasKey($key, $personalProfits, 'Не все данные партнерской программы');
      $this->assertEquals($val['landing_id'], $personalProfits[$key]['landing_id'], 'Лендинг неверен');
      $this->assertEquals($val['operator_id'], $personalProfits[$key]['operator_id'], 'Оператор неверен' );
      $this->assertEquals($val['rebill_percent'], $personalProfits[$key]['rebill_percent'], 'Процент за ребилл неверен');
      $this->assertEquals($val['buyout_percent'], $personalProfits[$key]['buyout_percent'], 'Процент выкупа неверен');
      $this->assertEquals($val['cpa_profit_' . $userCurrency], $personalProfits[$key]['cpa_profit_' . $userCurrency], 'CPA неверен');
    }
  }

  public function testChangePartnerProgramSync()
  {
    $userCurrency = UserPaymentSetting::findOne(['user_id' => 101])->currency;
    /** @var PartnerProgramSync|\PHPUnit_Framework_MockObject_MockObject $stub */
    $stub = $this->getMockBuilder(PartnerProgramSync::class)
      ->setMethods(['getPartnerProgramId'])
      ->setConstructorArgs(['config' => ['userId' => 101]])
      ->getMock();
    $stub->method('getPartnerProgramId')->willReturn(2);
    $stub->init();
    $stub->run();

    $personalProfits = $this->getPartnerProfit(101);
    $partnerProgramItems = $this->getPartnerProgramItems(2);
    $this->assertEquals(
      count($personalProfits),
      count($partnerProgramItems),
      'Количество personalProfits и partnerProgramItems для юзера должно быть одинаковым'
    );
    foreach ($partnerProgramItems as $key => $val) {
      $this->assertArrayHasKey($key, $personalProfits, 'Не все данные партнерской программы');
      $this->assertEquals($val['landing_id'], $personalProfits[$key]['landing_id'], 'Лендинг неверен');
      $this->assertEquals($val['operator_id'], $personalProfits[$key]['operator_id'], 'Оператор неверен' );
      $this->assertEquals($val['rebill_percent'], $personalProfits[$key]['rebill_percent'], 'Процент за ребилл неверен');
      $this->assertEquals($val['buyout_percent'], $personalProfits[$key]['buyout_percent'], 'Процент выкупа неверен');
      $this->assertEquals($val['cpa_profit_' . $userCurrency], $personalProfits[$key]['cpa_profit_' . $userCurrency], 'CPA неверен');
    }
  }

   public function testPartnerChangeWalletSync()
   {
     $settings = UserPaymentSetting::findOne(['user_id' => 101]);
     $settings->currency = 'usd';
     $settings->save();
     $userCurrency = UserPaymentSetting::findOne(['user_id' => 101])->currency;
     /** @var PartnerProgramSync|\PHPUnit_Framework_MockObject_MockObject $stub */
    $stub = $this->getMockBuilder(PartnerProgramSync::class)
      ->setMethods(['getPartnerProgramId'])
      ->setConstructorArgs(['config' => ['userId' => 101]])
      ->getMock();
    $stub->method('getPartnerProgramId')->willReturn(2);
    $stub->init();
    $stub->run();

    $personalProfits = $this->getPartnerProfit(101);
    $partnerProgramItems = $this->getPartnerProgramItems(2);
    $this->assertEquals(
      count($personalProfits),
      count($partnerProgramItems),
      'Количество personalProfits и partnerProgramItems для юзера должно быть одинаковым'
    );
    foreach ($partnerProgramItems as $key => $val) {
      $this->assertArrayHasKey($key, $personalProfits, 'Не все данные партнерской программы');
      $this->assertEquals($val['landing_id'], $personalProfits[$key]['landing_id'], 'Лендинг неверен');
      $this->assertEquals($val['operator_id'], $personalProfits[$key]['operator_id'], 'Оператор неверен' );
      $this->assertEquals($val['rebill_percent'], $personalProfits[$key]['rebill_percent'], 'Процент за ребилл неверен');
      $this->assertEquals($val['buyout_percent'], $personalProfits[$key]['buyout_percent'], 'Процент выкупа неверен');
      $this->assertEquals($val['cpa_profit_' . $userCurrency], $personalProfits[$key]['cpa_profit_' . $userCurrency], 'CPA неверен');
    }
  }

  public function testHasNoDuplicates()
  {
    /** @var PartnerProgramSync|\PHPUnit_Framework_MockObject_MockObject $stub */
    $stub = $this->getMockBuilder(PartnerProgramSync::class)
      ->setMethods(['getPartnerProgramId'])
      ->setConstructorArgs(['config' => ['userId' => 101]])
      ->getMock();
    $stub->method('getPartnerProgramId')->willReturn(1);
    $stub->init();
    $stub->run();

    $values = [];
    foreach ($this->getPartnerProfitQuery(101)->addSelect(['user_id'])->all() as $item) {
      $key = implode('_', [$item['user_id'], $item['landing_id'], $item['operator_id']]);
      $this->assertArrayNotHasKey($key, $values, 'Дублирование записи ' . $key);
      $values[$key] = $item;
    }

  }

  /**
   * @param $partnerProgramId
   * @return array
   */
  private function getPartnerProgramItems($partnerProgramId)
  {
    return ArrayHelper::map(
      (new Query())->from(PartnerProgramItem::tableName())->where(['partner_program_id' => $partnerProgramId])->all(),
      function ($item) {
        return implode('_', [(int)$item['landing_id'], (int)$item['operator_id']]);
      },
      function ($item) {
        // пустые значения заменит на нули
        return array_map('floatval', $item);
      }
    );
  }

  /**
   * @param $userId
   * @return Query
   */
  private function getPartnerProfitQuery($userId)
  {
    return (new Query())
      ->select(['operator_id', 'landing_id', 'rebill_percent', 'buyout_percent', 'cpa_profit_rub', 'cpa_profit_eur', 'cpa_profit_usd'])
      ->from(PersonalProfit::tableName())
      ->where(['user_id' => $userId])
    ;
  }

  /**
   * @param $userId
   * @return array
   */
  private function getPartnerProfit($userId)
  {
    return ArrayHelper::map(
      $this->getPartnerProfitQuery($userId)->all(),
      function ($item) {
        return implode('_', [$item['landing_id'], $item['operator_id']]);
      },
      function ($item) {
        return $item;
      }
    );
  }
}