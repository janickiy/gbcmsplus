<?php
namespace mcms\payments\components;

use mcms\payments\components\mgmp\send\ApiMgmpSender;
use mcms\payments\exceptions\InvoicesSyncException;
use mcms\payments\lib\mgmp\InvoicesTypeCaster;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Импорт инвойсов
 *
 * TRICKY Управление синхронизируемыми типами и для MCMS, и для MGMP выполняется в одном месте в MGMP
 * \common\modules\payments\components\InvoicesSync::$mgmpTypesToSync
 * TRICKY Импорт файлов и удаление записей не поддерживается
 *
 * Сценарий
 * - определяем текущую дату
 * - определяем дату последней синхронизации
 * - запрашиваем у внешнего сервиса инвойсы определенных типов, которые были созданы или обновлены позже даты последней синхронизации
 *
 * - если локально инвойса нет
 * -- инвойс заполняется полученными данными
 * -- дополнительно инвойсу присваивается external_id равный id во внешнем сервисе
 * -- в качестве updated_at устанавливается текущее время, что бы внешний сервис посчитал этот инвойс обновленным
 * и импортировал себе id этого инвойса и установил себе как external_id
 *
 * - если локально инвойс найден
 * -- инвойс заполняется полученными данными
 * -- в отличии от сценария когда инвойс не найден, дата обновления устанавливается такая же как у внешнего сервиса,
 *  что бы внешний сервис посчитал этот инвойс у себя актуальным и не импортировал себе
 *
 * - если синхронизация прошла полностью успешно, то есть обработались абсолютно все инвойсы, то обновляем дату синхронизации
 * - если же хотя бы один инвойс не синхронизировался, то дата не обновляется, что бы в дальнейшем повторить попытку
 * импорта
 *
 * TODO Многие наши синхронизаторы используют кэш для хранения даты последней синхронизации.
 * Нужно или перевести их на Rabbit, или хранить дату в БД
 */
class InvoicesImport extends Object
{
  /** @const int Длительность хранения даты последней синхронизации (месяц) */
  const KEEP_SYNC_DATE_DURATION = 2592000;

  /**
   * Выполнить импорт
   * @return bool
   */
  public function execute()
  {
    try {
      $this->executeInternal();
    } catch (InvoicesSyncException $exception) {
      \Yii::error(
        'Импорт инвойсов завершился ошибкой. Причина: ' . $exception->getName() . ' ' . $exception->getMessage(),
        __METHOD__
      );

      return false;
    }

    return true;
  }

  /**
   * Выполнить импорт
   */
  public function executeInternal()
  {
    $importDate = time();
    $importLastDate = $this->getImportLastDate();
    $invoices = $this->requestInvoices($importLastDate);
    if ($this->saveInvoices($invoices)) {
      $this->saveImportDate($importDate);
    }
  }

  /**
   * Сохранить инвойсы
   * @param array[] $externalInvoices Инвойсы
   * @return bool
   * true - все инвойсы обработаны
   * false - часть инвойсов не удалось обработать
   */
  public function saveInvoices(array $externalInvoices)
  {
    $isSuccess = true;
    /** @var array $extInvoice */
    foreach ($externalInvoices as $extInvoice) {
      $invoiceQuery = UserBalanceInvoice::find();
      if (!empty($extInvoice['external_id'])) {
        $invoiceQuery->andWhere(['id' => $extInvoice['external_id']]);
      } else if (!empty($extInvoice['id'])) {
        $invoiceQuery->andWhere(['mgmp_id' => $extInvoice['id']]);
      } else {
        Yii::error('При импорте инвойсов было получен инвойс не содержащий нужных идентификаторов. 
        Данные инвойса: ' . print_r($extInvoice, true), __METHOD__);
        $isSuccess = false;
        continue;
      }

      $invoice = $invoiceQuery->one() ?: new UserBalanceInvoice;

      // Если локальный инвойс новее полученного, то пропускаем его
      // Если у локальной записи нет mgmp_id, то она обновляется принудительно
      if (!$invoice->isNewRecord && $invoice->updated_at >= $extInvoice['updated_at'] && $invoice->mgmp_id) {
        continue;
      }

      if ($invoice->isNewRecord) {
        // Данный синк поддерживает только реселлерские инвойсы
        $invoice->user_id = UserPayment::getResellerId();
        $invoice->type = InvoicesTypeCaster::mgmp2mcms(ArrayHelper::getValue($extInvoice, 'type'), true);
      }
      if (!$invoice->mgmp_id) $invoice->mgmp_id = ArrayHelper::getValue($extInvoice, 'id');
      $invoice->amount = ArrayHelper::getValue($extInvoice, 'amount');
      $invoice->currency = ArrayHelper::getValue($extInvoice, 'currency');
      $invoice->description = ArrayHelper::getValue($extInvoice, 'comment');
      $invoice->date = ArrayHelper::getValue($extInvoice, 'date');
      $invoice->created_at = ArrayHelper::getValue($extInvoice, 'created_at');
      // Если запись новая, дата обновления обновляется,
      // что бы сервис из которого импортирована эта запись в дальнейшем посчитал обновленной и импортнул себе external_id
      $invoice->updated_at = $invoice->isNewRecord ? time() : ArrayHelper::getValue($extInvoice, 'updated_at');

      if (!$invoice->type) {
        Yii::error("Не удалось определить тип импортированного инвойса. External_id #{$invoice->mgmp_id}", __METHOD__);
        $isSuccess = false;
        continue;
      }

      if (!$invoice->save()) {
        Yii::error("Не удалось сохранить импортированный инвойс. External_id #{$invoice->mgmp_id}. 
        Errors: " . print_r($invoice->getErrors(), true), __METHOD__);
        $isSuccess = false;
        continue;
      }
    }

    return $isSuccess;
  }

  /**
   * Сохранение даты импорта.
   * TRICKY Дата импорта должна определяться до импорта, а не после, иначе изменения инвойсов, сделанные во время импорта,
   * будут утеряны, так как будут считаться уже импортированными
   * TRICKY Дата импорта должна устанавливаться только при полностью успешном выполнении импорта, иначе инвойсы,
   * которые не удалось импортировать, никогда не будут синхронизированы, так как будут считаться уже обработанными
   * @param int $importDate
   */
  private function saveImportDate($importDate)
  {
    if (!Yii::$app->cache->set($this->getSyncDateCacheKey(), $importDate, self::KEEP_SYNC_DATE_DURATION)) {
      Yii::warning('Не удалось сохранить дату последней синхронизации инвойсов', __METHOD__);
    }
  }

  /**
   * Дата последнего импорта
   * @return int|null
   */
  private function getImportLastDate()
  {
    return Yii::$app->cache->get($this->getSyncDateCacheKey());
  }

  /**
   * Получение инвойсов для импорта.
   * @param int $importLastDate Дата последней синхронизации
   * @return array[]
   * @throws InvoicesSyncException
   */
  protected function requestInvoices($importLastDate)
  {
    /** @var ApiMgmpSender $sender */
    $sender = Yii::createObject('mcms\payments\components\mgmp\send\MgmpSenderInterface');
    return ArrayHelper::getValue($sender->requestInvoices($importLastDate), 'data', []);
  }

  /**
   * Ключ для сохранения даты последней синхронизации
   * @return string
   */
  private function getSyncDateCacheKey()
  {
    return 'payment_invoices_sync_date';
  }
}