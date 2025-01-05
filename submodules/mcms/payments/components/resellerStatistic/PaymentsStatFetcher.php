<?php

namespace mcms\payments\components\resellerStatistic;

use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use mcms\statistic\models\resellerStatistic\PaymentsStatFetchInterface;
use mcms\statistic\models\resellerStatistic\PaymentStatItemInterface;
use rgk\utils\components\CurrenciesValues;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Достаем суммы для статистики реселлера
 *
 * Class PaymentsStatFetcher
 * @package mcms\payments\components\resellerStatistic
 */
class PaymentsStatFetcher implements PaymentsStatFetchInterface
{
  const WEEK = 'week';
  const DAY = 'day';
  const MONTH = 'month';

  const QUERY_TYPE_AWAITING = 'awaiting';
  const QUERY_TYPE_PAID = 'paid';

  /**
   * @var string|null тип группировки. $fakeGroupType если без группировки (для тоталс значений)
   */
  protected $groupType;
  /**
   * @var string фильтр по дате
   */
  protected $dateFrom;
  /**
   * @var string фильтр по дате
   */
  protected $dateTo;
  /**
   * @var string нужен для подстановки в селект если достаём totals значения.
   */
  protected $fakeGroupType;
  /**
   * @var PaymentStatItemInterface[]
   */
  protected $_models = [];

  /**
   * @inheritdoc
   */
  public function setGroupTypeDay()
  {
    $this->groupType = self::DAY;
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function setGroupTypeWeek()
  {
    $this->groupType = self::WEEK;
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function setGroupTypeMonth()
  {
    $this->groupType = self::MONTH;
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function setFakeGroupType($value)
  {
    $this->fakeGroupType = $value;
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function setDateFrom($value)
  {
    $this->dateFrom = $value;
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function setDateTo($value)
  {
    $this->dateTo = $value;
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getModels()
  {
    $this->fetchPaidData();
    $this->fetchAwaitingData();
    $this->fetchInvoicesData();

    return $this->_models;
  }


  /**
   * @param $type
   * @return Query
   */
  protected function createMainQuery($type)
  {
    $dateField = $type == self::QUERY_TYPE_AWAITING ? 'st.created_at' : 'st.payed_at';

    $query = (new Query())
      ->from(['st' => UserPayment::tableName()])
      ->addSelect([
        "res_{$type}_rub_count" => new Expression(
          "COUNT(DISTINCT CASE WHEN st.currency='rub' AND st.user_id=:rId THEN st.id END)"
        ),
        "res_{$type}_usd_count" => new Expression(
          "COUNT(DISTINCT CASE WHEN st.currency='usd' AND st.user_id=:rId THEN st.id END)"
        ),
        "res_{$type}_eur_count" => new Expression(
          "COUNT(DISTINCT CASE WHEN st.currency='eur' AND st.user_id=:rId THEN st.id END)"
        ),

        "part_{$type}_rub_count" => new Expression(
          "COUNT(DISTINCT CASE WHEN st.currency='rub' AND st.user_id<>:rId THEN st.id END)"
        ),
        "part_{$type}_usd_count" => new Expression(
          "COUNT(DISTINCT CASE WHEN st.currency='usd' AND st.user_id<>:rId THEN st.id END)"
        ),
        "part_{$type}_eur_count" => new Expression(
          "COUNT(DISTINCT CASE WHEN st.currency='eur' AND st.user_id<>:rId THEN st.id END)"
        ),

        "res_{$type}_rub" => new Expression("-SUM(IF(i.currency='rub' AND st.user_id = :rId,i.amount,0))"),
        "res_{$type}_usd" => new Expression("-SUM(IF(i.currency='usd' AND st.user_id = :rId,i.amount,0))"),
        "res_{$type}_eur" => new Expression("-SUM(IF(i.currency='eur' AND st.user_id = :rId,i.amount,0))"),

        "part_{$type}_rub" => new Expression("-SUM(IF(i.currency='rub' AND st.user_id <> :rId,i.amount,0))"),
        "part_{$type}_usd" => new Expression("-SUM(IF(i.currency='usd' AND st.user_id <> :rId,i.amount,0))"),
        "part_{$type}_eur" => new Expression("-SUM(IF(i.currency='eur' AND st.user_id <> :rId,i.amount,0))"),
      ])
      ->leftJoin(
        ['i' => UserBalanceInvoice::tableName()],
        "i.user_payment_id = st.id AND i.user_id = :rId"
      )
      ->addParams([':rId' => $this->getResellerId()]);

    $query->andFilterWhere(['>=', $dateField, strtotime($this->dateFrom)]);
    $query->andFilterWhere(['<=', $dateField, strtotime($this->dateTo . ' +1day') - 1]);

    if (!$this->groupType) {
      $query->addSelect(['group' => new Expression($this->fakeGroupType)]);
      return $query;
    }

    switch ($this->groupType) {
      case self::WEEK:
        $groupBy = "DATE_FORMAT(FROM_UNIXTIME($dateField) - INTERVAL WEEKDAY(FROM_UNIXTIME($dateField)) DAY, '%Y-%m-%d')";
        break;
      case self::MONTH:
        $groupBy = "DATE_FORMAT(FROM_UNIXTIME($dateField), '%Y-%m-01')";
        break;
      default:
        $groupBy = "DATE_FORMAT(FROM_UNIXTIME($dateField), '%Y-%m-%d')";
    }

    return $query
      ->addSelect(['group' => new Expression($groupBy)])
      ->groupBy('group')
      ->orderBy(['group' => SORT_DESC]);
  }

  private function fetchPaidData()
  {
    $query = $this->createMainQuery(self::QUERY_TYPE_PAID)
      ->andWhere([
        'or',
        [
          'and',
          ['<>', 'st.user_id', new Expression(':rId')],
          ['processing_type' => UserPayment::PROCESSING_TYPE_EXTERNAL]
        ],
        ['st.user_id' => new Expression(':rId')],
      ])
      ->andWhere(['status' => UserPayment::STATUS_COMPLETED]);

    foreach ($query->all() as $dbItem) {
      $model = $this->getModel($dbItem['group']);
      $model->resPaidCount = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'res_paid_rub_count', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'res_paid_usd_count', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'res_paid_eur_count', 0));
      $model->partPaidCount = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'part_paid_rub_count', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'part_paid_usd_count', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'part_paid_eur_count', 0));
      $model->resPaid = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'res_paid_rub', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'res_paid_usd', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'res_paid_eur', 0));
      $model->partPaid = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'part_paid_rub', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'part_paid_usd', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'part_paid_eur', 0));

      $this->_models[$dbItem['group']] = $model;
    }
  }

  private function fetchAwaitingData()
  {
    // tricky: В задаче MCMS-1619 добавил STATUS_COMPLETED в условие для реса и партнеров
    // tricky: чтобы в колонке Отправлено в RGK отображались также и выплаченые выплаты
    $query = $this->createMainQuery(self::QUERY_TYPE_AWAITING)
      ->andWhere([
        'or',
        [
          'and',
          ['<>', 'st.user_id', new Expression(':rId')],
          ['status' => [UserPayment::STATUS_COMPLETED, UserPayment::STATUS_PROCESS]],
          ['processing_type' => UserPayment::PROCESSING_TYPE_EXTERNAL]
        ],
        // Для реса другие статусы
        ['and', ['st.user_id' => new Expression(':rId')], ['status' => [
          UserPayment::STATUS_COMPLETED, UserPayment::STATUS_AWAITING, UserPayment::STATUS_DELAYED, UserPayment::STATUS_PROCESS
        ]]],
      ]);

    foreach ($query->all() as $dbItem) {
      $model = $this->getModel($dbItem['group']);
      $model->resAwaitCount = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'res_awaiting_rub_count', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'res_awaiting_usd_count', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'res_awaiting_eur_count', 0));
      $model->partAwaitCount = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'part_awaiting_rub_count', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'part_awaiting_usd_count', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'part_awaiting_eur_count', 0));
      $model->resAwait = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'res_awaiting_rub', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'res_awaiting_usd', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'res_awaiting_eur', 0));
      $model->partAwait = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'part_awaiting_rub', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'part_awaiting_usd', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'part_awaiting_eur', 0));

      $this->_models[$dbItem['group']] = $model;
    }
  }

  /**
   * @return int
   */
  protected function getResellerId()
  {
    return UserPayment::getResellerId();
  }

  /**
   * Получение штрафов/компенсаций
   */
  private function fetchInvoicesData()
  {
    $penalties = 'penalties_%s';
    $penaltiesCount = $penalties . '_count';
    $compensations = 'compensations_%s';
    $compensationsCount = $compensations . '_count';
    $convIncreases = 'conv_increases_%s';
    $convIncreasesCount = $convIncreases . '_count';
    $convDecreases = 'conv_decreases_%s';
    $convDecreasesCount = $convDecreases . '_count';
    $credits = 'credits_%s';
    $creditsCount = $credits . '_count';
    $creditCharges = 'credit_charges_%s';

    $sumSelect = "ABS(SUM(IF(currency='%s' AND type='%s', amount, 0)))";
    $countSelect =  "COUNT(DISTINCT CASE WHEN currency='%s' AND type='%s' THEN i.id END)";

    $creditChargesSelect = "ABS(SUM(IF(currency='%s' AND type IN (%s), amount, 0)))";

    $query = (new Query())
      ->from(['i' => UserBalanceInvoice::tableName()])
      ->addSelect([
        sprintf($penalties, 'rub') => new Expression(sprintf($sumSelect, 'rub', UserBalanceInvoice::TYPE_PENALTY)),
        sprintf($penalties, 'usd') => new Expression(sprintf($sumSelect, 'usd', UserBalanceInvoice::TYPE_PENALTY)),
        sprintf($penalties, 'eur') => new Expression(sprintf($sumSelect, 'eur', UserBalanceInvoice::TYPE_PENALTY)),
        sprintf($penaltiesCount, 'rub') => new Expression(sprintf($countSelect, 'rub', UserBalanceInvoice::TYPE_PENALTY)),
        sprintf($penaltiesCount, 'usd') => new Expression(sprintf($countSelect, 'usd', UserBalanceInvoice::TYPE_PENALTY)),
        sprintf($penaltiesCount, 'eur') => new Expression(sprintf($countSelect, 'eur', UserBalanceInvoice::TYPE_PENALTY)),
        sprintf($compensations, 'rub') => new Expression(sprintf($sumSelect, 'rub', UserBalanceInvoice::TYPE_COMPENSATION)),
        sprintf($compensations, 'usd') => new Expression(sprintf($sumSelect, 'usd', UserBalanceInvoice::TYPE_COMPENSATION)),
        sprintf($compensations, 'eur') => new Expression(sprintf($sumSelect, 'eur', UserBalanceInvoice::TYPE_COMPENSATION)),
        sprintf($compensationsCount, 'rub') => new Expression(sprintf($countSelect, 'rub', UserBalanceInvoice::TYPE_COMPENSATION)),
        sprintf($compensationsCount, 'usd') => new Expression(sprintf($countSelect, 'usd', UserBalanceInvoice::TYPE_COMPENSATION)),
        sprintf($compensationsCount, 'eur') => new Expression(sprintf($countSelect, 'eur', UserBalanceInvoice::TYPE_COMPENSATION)),
        sprintf($convDecreases, 'rub') => new Expression(sprintf($sumSelect, 'rub', UserBalanceInvoice::TYPE_CONVERT_DECREASE)),
        sprintf($convDecreases, 'usd') => new Expression(sprintf($sumSelect, 'usd', UserBalanceInvoice::TYPE_CONVERT_DECREASE)),
        sprintf($convDecreases, 'eur') => new Expression(sprintf($sumSelect, 'eur', UserBalanceInvoice::TYPE_CONVERT_DECREASE)),
        sprintf($convDecreasesCount, 'rub') => new Expression(sprintf($countSelect, 'rub', UserBalanceInvoice::TYPE_CONVERT_DECREASE)),
        sprintf($convDecreasesCount, 'usd') => new Expression(sprintf($countSelect, 'usd', UserBalanceInvoice::TYPE_CONVERT_DECREASE)),
        sprintf($convDecreasesCount, 'eur') => new Expression(sprintf($countSelect, 'eur', UserBalanceInvoice::TYPE_CONVERT_DECREASE)),
        sprintf($convIncreases, 'rub') => new Expression(sprintf($sumSelect, 'rub', UserBalanceInvoice::TYPE_CONVERT_INCREASE)),
        sprintf($convIncreases, 'usd') => new Expression(sprintf($sumSelect, 'usd', UserBalanceInvoice::TYPE_CONVERT_INCREASE)),
        sprintf($convIncreases, 'eur') => new Expression(sprintf($sumSelect, 'eur', UserBalanceInvoice::TYPE_CONVERT_INCREASE)),
        sprintf($convIncreasesCount, 'rub') => new Expression(sprintf($countSelect, 'rub', UserBalanceInvoice::TYPE_CONVERT_INCREASE)),
        sprintf($convIncreasesCount, 'usd') => new Expression(sprintf($countSelect, 'usd', UserBalanceInvoice::TYPE_CONVERT_INCREASE)),
        sprintf($convIncreasesCount, 'eur') => new Expression(sprintf($countSelect, 'eur', UserBalanceInvoice::TYPE_CONVERT_INCREASE)),

        sprintf($credits, 'rub') => new Expression(sprintf($sumSelect, 'rub', UserBalanceInvoice::TYPE_CREDIT_ACCRUE_AMOUNT)),
        sprintf($credits, 'usd') => new Expression(sprintf($sumSelect, 'usd', UserBalanceInvoice::TYPE_CREDIT_ACCRUE_AMOUNT)),
        sprintf($credits, 'eur') => new Expression(sprintf($sumSelect, 'eur', UserBalanceInvoice::TYPE_CREDIT_ACCRUE_AMOUNT)),
        sprintf($creditsCount, 'rub') => new Expression(sprintf($countSelect, 'rub', UserBalanceInvoice::TYPE_CREDIT_ACCRUE_AMOUNT)),
        sprintf($creditsCount, 'usd') => new Expression(sprintf($countSelect, 'usd', UserBalanceInvoice::TYPE_CREDIT_ACCRUE_AMOUNT)),
        sprintf($creditsCount, 'eur') => new Expression(sprintf($countSelect, 'eur', UserBalanceInvoice::TYPE_CREDIT_ACCRUE_AMOUNT)),
        sprintf($creditCharges, 'rub') => new Expression(
          sprintf(
            $creditChargesSelect,
            'rub',
            implode(',', [UserBalanceInvoice::TYPE_CREDIT_BALANCE_PAYMENT, UserBalanceInvoice::TYPE_CREDIT_MONTHLY_FEE])
          )
        ),
        sprintf($creditCharges, 'usd') => new Expression(
          sprintf(
            $creditChargesSelect,
            'usd',
            implode(',', [UserBalanceInvoice::TYPE_CREDIT_BALANCE_PAYMENT, UserBalanceInvoice::TYPE_CREDIT_MONTHLY_FEE])
          )
        ),
        sprintf($creditCharges, 'eur') => new Expression(
          sprintf(
            $creditChargesSelect,
            'eur',
            implode(',', [UserBalanceInvoice::TYPE_CREDIT_BALANCE_PAYMENT, UserBalanceInvoice::TYPE_CREDIT_MONTHLY_FEE])
          )
        ),
      ])
      ->andWhere(['not in', 'type', [UserBalanceInvoice::TYPE_PAYMENT]])
      ->andFilterWhere(['>=', 'date', $this->dateFrom])
      ->andFilterWhere(['<=', 'date', $this->dateTo])
      ->andWhere(['user_id' => $this->getResellerId()]);

    if (!$this->groupType) {
      $query->addSelect(['group' => new Expression($this->fakeGroupType)]);
    } else {
      switch ($this->groupType) {
        case self::WEEK:
          $groupBy = "DATE_FORMAT(date - INTERVAL WEEKDAY(date) DAY, '%Y-%m-%d')";
          break;
        case self::MONTH:
          $groupBy = "DATE_FORMAT(date, '%Y-%m-01')";
          break;
        default:
          $groupBy = "DATE_FORMAT(date, '%Y-%m-%d')";
      }

      $query
        ->addSelect(['group' => new Expression($groupBy)])
        ->groupBy('group')
        ->orderBy(['group' => SORT_DESC]);
    }

    foreach ($query->all() as $dbItem) {
      $model = $this->getModel($dbItem['group']);
      $model->penalties = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, sprintf($penalties, 'rub'), 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, sprintf($penalties, 'usd'), 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, sprintf($penalties, 'eur'), 0));
      $model->penaltiesCount = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, sprintf($penaltiesCount, 'rub'), 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, sprintf($penaltiesCount, 'usd'), 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, sprintf($penaltiesCount, 'eur'), 0));
      $model->compensations = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, sprintf($compensations, 'rub'), 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, sprintf($compensations, 'usd'), 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, sprintf($compensations, 'eur'), 0));
      $model->compensationsCount = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, sprintf($compensationsCount, 'rub'), 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, sprintf($compensationsCount, 'usd'), 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, sprintf($compensationsCount, 'eur'), 0));
      $model->convertIncreases = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, sprintf($convIncreases, 'rub'), 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, sprintf($convIncreases, 'usd'), 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, sprintf($convIncreases, 'eur'), 0));
      $model->convertIncreasesCount = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, sprintf($convIncreasesCount, 'rub'), 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, sprintf($convIncreasesCount, 'usd'), 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, sprintf($convIncreasesCount, 'eur'), 0));
      $model->convertDecreases = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, sprintf($convDecreases, 'rub'), 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, sprintf($convDecreases, 'usd'), 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, sprintf($convDecreases, 'eur'), 0));
      $model->convertDecreasesCount = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, sprintf($convDecreasesCount, 'rub'), 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, sprintf($convDecreasesCount, 'usd'), 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, sprintf($convDecreasesCount, 'eur'), 0));
      $model->credits = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, sprintf($credits, 'rub'), 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, sprintf($credits, 'usd'), 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, sprintf($credits, 'eur'), 0));
      $model->creditsCount = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, sprintf($creditsCount, 'rub'), 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, sprintf($creditsCount, 'usd'), 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, sprintf($creditsCount, 'eur'), 0));
      $model->creditCharges = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, sprintf($creditCharges, 'rub'), 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, sprintf($creditCharges, 'usd'), 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, sprintf($creditCharges, 'eur'), 0));

      $this->_models[$dbItem['group']] = $model;
    }
  }


  /**
   * @param $groupValue
   * @return PaymentStatItem
   */
  protected function getModel($groupValue)
  {
    return ArrayHelper::getValue(
      $this->_models,
      $groupValue,
      new PaymentStatItem(['groupValue' => $groupValue])
    );
  }
}