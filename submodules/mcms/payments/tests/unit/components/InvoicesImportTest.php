<?php
namespace mcms\payments\tests\components;

use Codeception\Util\Stub;
use mcms\common\codeception\TestCase;
use mcms\payments\components\InvoicesImport;
use mcms\payments\models\UserBalanceInvoice;

/**
 * Тест импорта реселлерских инвойсов
 */
class InvoicesImportTest extends TestCase
{
  /**
   * @inheritdoc
   */
  protected function setUp()
  {
    $this->loginAsRoot();
    parent::setUp();
  }

  /**
   * Проверка, что важные поля не могут быть изменены при синхронизации для существующих инвойсов
   */
  public function testReplaceCriticData()
  {
    UserBalanceInvoice::deleteAll();

    // Создаем инвойс
    $invoiceData = [
      'id' => 1,
      'mgmp_id' => 2,
      'user_id' => 4,
      'type' => UserBalanceInvoice::TYPE_CONVERT_INCREASE,
      'amount' => 777,
      'currency' => 'usd',
      'created_at' => time(),
      'updated_at' => time(),
    ];
    $this->assertTrue((new UserBalanceInvoice($invoiceData))->save(), 'Не удалось создать инвойс');

    // Подготавливаем новую инфу для инвойса
    $import = Stub::make(InvoicesImport::class, [
      'requestInvoices' => [
        [
          'external_id' => 1,
          'id' => 5,
          'user_id' => 3,
          'amount' => 999,
          'currency' => 'rub',
          'type' => 7,
          // +777 что бы даты различались
          'created_at' => $invoiceData['created_at'] + 777,
          'updated_at' => $invoiceData['updated_at'] + 777,
        ]
      ],
    ]);

    // Импортируем новую инфу инвойса
    $this->assertTrue($import->execute(), 'Не удалось импортировать инвойсы');

    $invoice = UserBalanceInvoice::findOne($invoiceData['id']);
    $this->assertNotNull($invoice, 'Не удалось найти импортированный инвойс');

    // Убеждаемся, что инвойс импортирован
    $this->assertNotEquals($invoiceData['created_at'], $invoice->created_at, 'Данные инвойса не изменились после импорта');

    // Убеждаемся, что не смотря на кривые данные для импорта, инвойс не испорчен
    $this->assertEquals($invoiceData['mgmp_id'], $invoice->mgmp_id, 'Скрипт импорта позволил изменить mgmp_id инвойса');
    $this->assertEquals($invoiceData['user_id'], $invoice->user_id, 'Скрипт импорта позволил изменить user_id инвойса');
    $this->assertEquals($invoiceData['type'], $invoice->type, 'Скрипт импорта позволил изменить type инвойса');
  }
}