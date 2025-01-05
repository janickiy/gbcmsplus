<?php

namespace mcms\statistic\models\resellerStatistic;

use mcms\statistic\components\ResellerProfits;
use rgk\utils\components\CurrenciesValues;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Модель поиска статистики.
 *
 * Class ResellerProfitSearch1
 * @property string $dateFrom
 * @property string $dateTo
 * @package mcms\statistic\models\resellerStatistic
 */
class ItemSearch extends Model implements ItemSearchInterface
{
  /**
   * фильтр по диапазону дат разделен этой строкой
   */
  const DATE_RANGE_DELIMITER = ' - ';

  /**
   * Нужен для тоталс значений, подставляется в селект, используется в виде ключа массива.
   */
  const FAKE_GROUP = 0;

  /**
   * фильтр по дате
   * @var string например 2017-07-01 - 2017-07-08
   */
  public $dateRange;
  /**
   * @var null|string тип группировки в стате
   * @see Group::$groupType
   */
  public $groupType;

  /** @var  string */
  private $_dateFrom;
  /** @var  string */
  private $_dateTo;

  /**
   * кэшируем наши модельки
   * @var Item[]
   */
  protected $_models = [];
  /**
   * кэшируем результаты для строки ИТОГО
   * @var array
   */
  private $_fieldResults;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['dateRange', 'groupType'], 'safe'],
    ];
  }

  /**
   * @return string
   */
  public function getDateFrom()
  {
    return $this->_dateFrom;
  }

  /**
   * @return string
   */
  public function getDateTo()
  {
    return $this->_dateTo;
  }

  /**
   * @return string[]
   */
  protected function getDefaultDateRange()
  {
    if ($this->groupType === Group::WEEK) {
      // показываем 10 недель
      $num = date('N', strtotime('today')) - 1;
      $countDaysMinus = $num + 9 * 7;
      return [
        Yii::$app->formatter->asDate("-{$countDaysMinus}days", 'php:Y-m-d'),
        Yii::$app->formatter->asDate('today', 'php:Y-m-d')
      ];
    }

    if ($this->groupType === Group::MONTH) {
      // показываем 10 месяцев
      return [
        Yii::$app->formatter->asDate('-9months', 'php:Y-m-01'),
        Yii::$app->formatter->asDate('today', 'php:Y-m-d')
      ];
    }

    if ($this->groupType === Group::DAY) {
      // показываем 10 дней
      return [
        Yii::$app->formatter->asDate('-9days', 'php:Y-m-d'),
        Yii::$app->formatter->asDate('today', 'php:Y-m-d')
      ];
    }

    return [];
  }

  /**
   * @param array $data
   * @param null $formName
   * @return bool
   */
  public function load($data, $formName = null)
  {
    $status = parent::load($data, $formName);

    // инициализация фильтра по датам
    $this->dateRange = $this->dateRange ?: implode(self::DATE_RANGE_DELIMITER, $this->getDefaultDateRange());
    if (!empty($this->dateRange) && strpos($this->dateRange, '-') !== false) {
      list($this->_dateFrom, $this->_dateTo) = explode(self::DATE_RANGE_DELIMITER, $this->dateRange);
      $this->correctDateFilters();
    }

    return $status;
  }


  /**
   * Метод для поиска по данным статистики.
   * Вернёт ArrayDataProvider с моделями Item
   * @param array $requestData
   * @return ArrayDataProvider
   */
  public function search(array $requestData)
  {
    $this->load($requestData);

    if (!$this->validate()) return new ArrayDataProvider(['allModels' => []]);

    $this->fetchProfitsData();
    $this->fetchPaymentsData();
    $this->fetchUnholdedData();

    return new ArrayDataProvider([
      'allModels' => $this->_models,
      'pagination' => false,
      'sort' => [
        'attributes' => ['group'],
        'defaultOrder' => ['group' => SORT_DESC]
      ]
    ]);
  }

  /**
   * @return string[]
   */
  public function getGroupByLabel()
  {
    return Group::getLabels($this->groupType);
  }

  /**
   * Оборот реса
   */
  protected function fetchProfitsData()
  {
    $query = (new Query())
      ->select([
        'res_profit_rub' => new Expression('SUM(profit_rub)'),
        'res_profit_revshare_rub' => new Expression('SUM(profit_revshare_rub)'),
        'res_profit_cpa_sold_rub' => new Expression('SUM(profit_cpa_sold_rub)'),
        'res_profit_cpa_rejected_rub' => new Expression('SUM(profit_cpa_rejected_rub)'),
        'res_profit_onetime_rub' => new Expression('SUM(profit_onetime_rub)'),

        'res_profit_usd' => new Expression('SUM(profit_usd)'),
        'res_profit_revshare_usd' => new Expression('SUM(profit_revshare_usd)'),
        'res_profit_cpa_sold_usd' => new Expression('SUM(profit_cpa_sold_usd)'),
        'res_profit_cpa_rejected_usd' => new Expression('SUM(profit_cpa_rejected_usd)'),
        'res_profit_onetime_usd' => new Expression('SUM(profit_onetime_usd)'),

        'res_profit_eur' => new Expression('SUM(profit_eur)'),
        'res_profit_revshare_eur' => new Expression('SUM(profit_revshare_eur)'),
        'res_profit_cpa_sold_eur' => new Expression('SUM(profit_cpa_sold_eur)'),
        'res_profit_cpa_rejected_eur' => new Expression('SUM(profit_cpa_rejected_eur)'),
        'res_profit_onetime_eur' => new Expression('SUM(profit_onetime_eur)'),
      ])
      ->from(['st' => ResellerProfits::tableName()]);

    $this->groupProfitsQuery($query);
    
    $query->andFilterWhere(['>=', 'date', $this->_dateFrom]);
    $query->andFilterWhere(['<=', 'date', $this->_dateTo]);

    foreach ($query->all() as $dbItem) {
      $model = $this->getModel($dbItem['group']);
      $model->resProfit = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'res_profit_rub', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'res_profit_usd', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'res_profit_eur', 0));
      $model->resProfitRevshare = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'res_profit_revshare_rub', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'res_profit_revshare_usd', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'res_profit_revshare_eur', 0));
      $model->resProfitCpaSold = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'res_profit_cpa_sold_rub', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'res_profit_cpa_sold_usd', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'res_profit_cpa_sold_eur', 0));
      $model->resProfitCpaRejected = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'res_profit_cpa_rejected_rub', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'res_profit_cpa_rejected_usd', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'res_profit_cpa_rejected_eur', 0));
      $model->resProfitOnetime = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'res_profit_onetime_rub', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'res_profit_onetime_usd', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'res_profit_onetime_eur', 0));
      $model->holded = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'holded_rub', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'holded_usd', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'holded_eur', 0));
      $this->_models[$dbItem['group']] = $model;
    }
  }


  /**
   * @param Query $query
   */
  protected function groupProfitsQuery(Query $query)
  {
    $query->addSelect([
      'holded_rub' => new Expression('SUM(IF(CURRENT_DATE() < unhold_date, profit_rub, 0))'),
      'holded_usd' => new Expression('SUM(IF(CURRENT_DATE() < unhold_date, profit_usd, 0))'),
      'holded_eur' => new Expression('SUM(IF(CURRENT_DATE() < unhold_date, profit_eur, 0))'),
    ]);

    if (!$this->groupType) {
      $query->addSelect([
        'group' => new Expression(self::FAKE_GROUP),
        'holded_rub' => new Expression('SUM(IF(CURRENT_DATE() < unhold_date, profit_rub, 0))'),
        'holded_usd' => new Expression('SUM(IF(CURRENT_DATE() < unhold_date, profit_usd, 0))'),
        'holded_eur' => new Expression('SUM(IF(CURRENT_DATE() < unhold_date, profit_eur, 0))'),
      ]);
      return;
    }

    $groupField = 'date';
    if ($this->groupType === Group::WEEK) $groupField =  'week_start';
    if ($this->groupType === Group::MONTH) $groupField =  'month_start';

    $query
      ->addSelect(['group' => $groupField])
      ->groupBy($groupField)
      ->orderBy([$groupField => SORT_DESC]);
  }
  /**
   * @param $groupValue
   * @return Item
   */
  protected function getModel($groupValue)
  {
    return ArrayHelper::getValue(
      $this->_models,
      $groupValue,
      new Item([
        'group' => $this->groupType
          ? new Group(['groupType' => $this->groupType, 'value' => $groupValue, 'searchModel' => $this])
          : null,
        'searchModel' => $this
      ])
    );
  }

  /**
   * Выполненные выплаты
   */
  protected function fetchPaymentsData()
  {
    // Пожалуй единственный тут костыль.
    // Надо чтобы модуль payments проинициализировался, иначе в контейнере не будет нужной реализации класса
    Yii::$app->getModule('payments');

    /** @var PaymentsStatFetchInterface $fetcher */
    $fetcher = Yii::$container->get(PaymentsStatFetchInterface::class);
    $fetcher
      ->setDateFrom($this->dateFrom)
      ->setDateTo($this->dateTo)
      ->setFakeGroupType(self::FAKE_GROUP);

    switch ($this->groupType) {
      case Group::WEEK:
        $fetcher->setGroupTypeWeek();
        break;
      case Group::DAY:
        $fetcher->setGroupTypeDay();
        break;
      case Group::MONTH:
        $fetcher->setGroupTypeMonth();
        break;
    }

    foreach ($fetcher->getModels() as $item) {
      $model = $this->getModel($item->getGroupValue());

      $model->resPaidCount = $item->getResPaidCount();
      $model->partPaidCount = $item->getPartPaidCount();
      $model->resPaid = $item->getResPaid();
      $model->partPaid = $item->getPartPaid();

      $model->resAwaitCount = $item->getResAwaitCount();
      $model->partAwaitCount = $item->getPartAwaitCount();
      $model->resAwait = $item->getResAwait();
      $model->partAwait = $item->getPartAwait();

      $model->penalties = $item->getPenalties();
      $model->penaltiesCount = $item->getPenaltiesCount();
      $model->compensations = $item->getCompensations();
      $model->compensationsCount = $item->getCompensationsCount();

      $model->convertIncreases = $item->getConvertIncreases();
      $model->convertIncreasesCount = $item->getConvertIncreasesCount();
      $model->convertDecreases = $item->getConvertDecreases();
      $model->convertDecreasesCount = $item->getConvertDecreasesCount();

      $model->credits = $item->getCredits();
      $model->creditsCount = $item->getCreditsCount();
      $model->creditCharges = $item->getCreditCharges();

      $this->_models[$item->getGroupValue()] = $model;
    }
  }

  /**
   * Суммы по расхолденным средствам
   */
  protected function fetchUnholdedData()
  {
    $query = (new Query())
      ->select([
        'unholded_rub' => new Expression('SUM(profit_rub)'),
        'unholded_usd' => new Expression('SUM(profit_usd)'),
        'unholded_eur' => new Expression('SUM(profit_eur)'),
      ])
      ->from(['st' => ResellerProfits::tableName()]);

    $this->groupUnholdedQuery($query);

    $query->andFilterWhere(['>=', 'unhold_date', $this->_dateFrom]);
    $query->andFilterWhere(['<=', 'unhold_date', $this->_dateTo ?: new Expression('CURRENT_DATE()')]);

    foreach ($query->all() as $dbItem) {
      $model = $this->getModel($dbItem['group']);
      $model->unholded = (new CurrenciesValues())
        ->setValue('rub', ArrayHelper::getValue($dbItem, 'unholded_rub', 0))
        ->setValue('usd', ArrayHelper::getValue($dbItem, 'unholded_usd', 0))
        ->setValue('eur', ArrayHelper::getValue($dbItem, 'unholded_eur', 0));
      $this->_models[$dbItem['group']] = $model;
    }
  }

  /**
   * @param Query $query
   */
  protected function groupUnholdedQuery(Query $query)
  {
    if (!$this->groupType) {
      $query->addSelect(['group' => new Expression(self::FAKE_GROUP)]);
      return;
    }

    $groupField = 'unhold_date';
    if ($this->groupType === Group::WEEK) $groupField = 'unhold_week_start';
    if ($this->groupType === Group::MONTH) $groupField = 'unhold_month_start';

    $query
      ->addSelect(['group' => $groupField])
      ->groupBy($groupField)
      ->orderBy([$groupField => SORT_DESC]);
  }

  /**
   * Считает ИТОГО для указанного поля.
   * Считает как сумму из уже найденной статы
   * TRICKY сперва надо вызвать search() чтобы было что суммировать.
   *
   * @param $field
   * @return CurrenciesValues
   */
  public function getResultValue($field)
  {
    if (is_null($this->_models)) return null;

    if (isset($this->_fieldResults[$field])) return $this->_fieldResults[$field];

    $sums = CurrenciesValues::createEmpty();

    foreach ($this->_models as $item) {
      /** @var Item $item */
      $values = ArrayHelper::getValue($item, $field);

      if (!$values instanceof CurrenciesValues) continue;

      $sums->plusValues($values);
    }

    return $this->_fieldResults[$field] = $sums;
  }

  /**
   * Откорректируем фильтр по датам. Например если выставишь 2017-07-11 - 2017-07-20 в группировке по месяцам, то ты
   * не увидишь остаток под конец диапазона, т.к. в расчет вошли не все новые выплаты
   * т.е. выплаты за дату от 1 до 11 июля проигнорятся в расчёте.
   *
   * Поэтому мы должны в фильтре показать то что ввёл пользователь, но расчеты вести полными диапазонами месяцев-недель.
   *
   * Корректируем только левую границу фильтра. Правая норм.
   */
  private function correctDateFilters()
  {
    if ($this->groupType == Group::DAY) return;

    if ($this->_dateFrom) {
      $this->_dateFrom = (new Query())
        ->select($this->groupType == Group::MONTH ? 'month_start' : 'week_start')
        ->from('days')
        ->where(['date' => $this->_dateFrom])
        ->scalar();
    }

    if ($this->_dateTo) {
      $this->_dateTo = (new Query())
        ->select($this->groupType == Group::MONTH ? 'month_end' : 'week_end')
        ->from('days')
        ->where(['date' => $this->_dateTo])
        ->scalar();
    }
  }
}