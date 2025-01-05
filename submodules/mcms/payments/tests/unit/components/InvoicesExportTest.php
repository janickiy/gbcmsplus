<?php
namespace mcms\payments\tests\components;

use mcms\common\codeception\TestCase;
use mcms\common\helpers\ArrayHelper;
use mcms\payments\components\InvoicesExport;
use mcms\payments\models\UserBalanceInvoice;

/**
 * Тест экспорта реселлерских инвойсов
 */
class InvoicesExportTest extends TestCase
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
   * Проверка, что в экспорт попадают инвойсы только указанного типа и только привязанные к реселлеру
   */
  public function testExportQuery()
  {
    UserBalanceInvoice::deleteAll();

    // Набор типов, которые надо синхронизировать
    $syncTypes = [UserBalanceInvoice::TYPE_CONVERT_INCREASE, UserBalanceInvoice::TYPE_CONVERT_DECREASE];

    // Инвойс с правильным user_id и type
    $invoiceCorrect = $this->createInvoice(4, $syncTypes[1]);
    // Инвойс с правильным user_id, но не правильным type
    $invoiceIncorrectType = $this->createInvoice(4, UserBalanceInvoice::TYPE_COMPENSATION);
    // Инвойс с правильным type, но не правильным user_id
    $invoiceIncorrectUser = $this->createInvoice(3, $syncTypes[1]);

    $invoicesToExport = (new InvoicesExport(['types' => $syncTypes]))->getInvoices();
    $invoicesIdToExport = ArrayHelper::getColumn($invoicesToExport, 'id');

    $this->assertContains($invoiceCorrect->id, $invoicesIdToExport, 'Корректный инвойс не попал в массив для экспорта');
    $this->assertNotContains($invoiceIncorrectType->id, $invoicesIdToExport, 'Инвойс с неподходящим типом попал в массив для экспорта');
    $this->assertNotContains($invoiceIncorrectUser->id, $invoicesIdToExport, 'Партнерский инвойс попал в массив для экспорта');
  }

  /**
   * Создать инвойс
   * @param $userId
   * @param $type
   * @return UserBalanceInvoice
   */
  private function createInvoice($userId, $type)
  {
    $invoice = new UserBalanceInvoice;
    $invoice->user_id = $userId;
    $invoice->currency = 'rub';
    $invoice->amount = 777;
    $invoice->description = 'Funky monkey';
    $invoice->type = $type;
    $invoice->file = null;
    $invoice->date = date('Y-m-d');

    $this->assertTrue($invoice->save(), 'Не удалось создать инвойс');

    return $invoice;
  }
}