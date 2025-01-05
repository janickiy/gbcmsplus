<?php

namespace mcms\statistic\components;

use mcms\payments\models\search\UserPaymentSearch;
use mcms\payments\models\UserPayment;
use mcms\statistic\models\resellerStatistic\Item;
use mcms\statistic\models\resellerStatistic\ItemSearch;
use mcms\statistic\models\resellerStatistic\ItemSearchInterface;
use Yii;
use yii\base\Object;

/**
 * Ссылка на список выплат, отфильтрованный по нужным полям. Подставляется в стате в столбцах Выплачено и В Ожидании
 */
class ResellerStatisticPaymentsLink extends Object
{
  const PARTNERS = 'partners';
  const RESELLERS = 'resellers';

  const PAID = 'paid';
  const AWAITING = 'awaiting';

  /**
   * @param Item $item
   * @param $type
   * @param $status
   * @param $currency
   * @return array
   */
  public function getItemLink(Item $item, $type, $status, $currency)
  {
    $params = [
      'currency' => $currency
    ];

    $dateRange = $this->getDateRange($item);

    if ($status == self::PAID) {
      $params['payed_at_range'] = $dateRange;
      $params['status'] = UserPayment::STATUS_COMPLETED;
    } else {
      $params['created_at_range'] = $dateRange;

      if ($type == self::PARTNERS) {
        $params['status'] = UserPayment::STATUS_PROCESS;
      } else {
        $params['status'] = [UserPayment::STATUS_AWAITING, UserPayment::STATUS_DELAYED, UserPayment::STATUS_PROCESS];
      }

    }

    if ($type == self::PARTNERS) {
      $params['processing_type'] = UserPayment::PROCESSING_TYPE_EXTERNAL;
    }

    return [$this->getRoute($type), (new UserPaymentSearch)->formName() => $params];
  }

  /**
   * @param ItemSearchInterface $searchModel
   * @param $type
   * @param $status
   * @param $currency
   * @return array
   */
  public function getFooterLink(ItemSearchInterface $searchModel, $type, $status, $currency)
  {
    $params = [
      'currency' => $currency
    ];

    // TRICKY Кустом! Не перезаписывать с горяча
    if ($status == self::PAID) {
      $params['payed_at_range'] = $searchModel->dateRange;
      $params['status'] = UserPayment::STATUS_COMPLETED;
    } else {
      $params['created_at_range'] = $searchModel->dateRange;

      if ($type == self::PARTNERS) {
        $params['status'] = UserPayment::STATUS_PROCESS;
      } else {
        $params['status'] = [UserPayment::STATUS_AWAITING, UserPayment::STATUS_DELAYED, UserPayment::STATUS_PROCESS];
      }
    }
    if ($type == self::PARTNERS) {
      $params['processing_type'] = UserPayment::PROCESSING_TYPE_EXTERNAL;
    }

    return [$this->getRoute($type), (new UserPaymentSearch)->formName() => $params];
  }


  /**
   * @param $type
   * @return string
   */
  protected function getRoute($type)
  {
    if ($type === self::PARTNERS) {
      return '/payments/payments/index';
    } else {
      return '/payments/reseller-checkout/index';
    }
  }

  /**
   * @param Item $item
   * @return string
   */
  protected function getDateRange($item)
  {
    return $item->group ? sprintf(
      '%s - %s',
      $item->group->getDateLeftValue(),
      $item->group->getDateRightValue()
    ) : null;
  }
}