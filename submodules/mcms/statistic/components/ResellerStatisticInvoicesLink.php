<?php

namespace mcms\statistic\components;

use mcms\payments\models\search\ResellerInvoiceSearch;
use mcms\payments\models\UserBalanceInvoice;
use mcms\statistic\models\resellerStatistic\Item;
use mcms\statistic\models\resellerStatistic\ItemSearch;
use mcms\statistic\models\resellerStatistic\ItemSearchInterface;
use yii\base\Object;

/**
 * Ссылка на список штрафов/компенсаций, отфильтрованный по нужным полям. Подставляется в стате
 *
 * Class ResellerStatisticInvoicesLink
 */
class ResellerStatisticInvoicesLink extends Object
{
  const PENALTY = 'penalty';
  const COMPENSATION = 'compensation';

  /**
   * @param Item $item
   * @param $type
   * @param $currency
   * @return array
   */
  public function getItemLink($item, $type, $currency)
  {
    $params = [
      'currency' => $currency,
      'type' => $type == self::COMPENSATION ? UserBalanceInvoice::TYPE_COMPENSATION : UserBalanceInvoice::TYPE_PENALTY
    ];

    if ($item->group) {
      $params['dateDateRange'] = $this->getDateRange($item);
    }

    return [$this->getRoute(), (new ResellerInvoiceSearch())->formName() => $params];
  }

  /**
   * @param ItemSearchInterface $searchModel
   * @param $type
   * @param $currency
   * @return array
   */
  public function getFooterLink(ItemSearchInterface $searchModel, $type, $currency)
  {
    $params = [
      'type' => $type == self::COMPENSATION ? UserBalanceInvoice::TYPE_COMPENSATION : UserBalanceInvoice::TYPE_PENALTY,
      'currency' => $currency
    ];

    $params['dateDateRange'] = $searchModel->dateRange;

    return [$this->getRoute(), (new ResellerInvoiceSearch())->formName() => $params];
  }


  /**
   * @return string
   */
  protected function getRoute()
  {
    return '/payments/reseller-invoices/index';
  }

  /**
   * @param Item $item
   * @return string
   */
  protected function getDateRange($item)
  {
    return $value = sprintf(
      '%s - %s',
      $item->group->getDateLeftValue(),
      $item->group->getDateRightValue()
    );
  }
}