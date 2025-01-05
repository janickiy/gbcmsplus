<?php

namespace mcms\statistic\components\grid;

use DateTime;
use mcms\common\helpers\ArrayHelper;
use mcms\statistic\models\Complain;
use mcms\statistic\models\mysql\Statistic;
use mcms\statistic\Module;
use Yii;
use yii\grid\DataColumn;
use yii\helpers\Html;
use yii\web\View;

class StatisticGrid extends \mcms\common\widget\AdminGridView
{

  const TRAFFIC = 'traffic';
  const REVSHARE = 'revshare';
  const CPA = 'cpa';
  const GROUP = 'group';
  const TOTAL = 'total';

  /**
   * Класс, который нужно подставить в столбцы с числами, чтобы сортировка велась корректно
   */
  const CLASS_INT_COL = 'datatable-int-col';

  public $resizableColumns = false;

  public $options = [
//    'class' => 'grid-view',
//    'style' => 'overflow:auto'
  ];

  public $layout = '{items}';

  public $export = false;
  public $condensed = true;
  public $responsiveWrap = false;
  public $emptyCell = '';

  /**
   * @var \mcms\statistic\models\mysql\Statistic
   */
  public $statisticModel;

  public $dataColumnClass = 'mcms\statistic\components\grid\StatisticColumn';

  /**
   * @var array
   */
  private static $gridColumns = [];

  public function init()
  {
    $this->columns = self::getGridColums($this->statisticModel);

    $this->tableOptions = [
      'class' => 'table nowrap text-center data-table dataTable',
      'id' => 'statistic-data-table',
      'data-skip-summary-calculation' => '0',
      'data-empty-result' => Yii::t('yii', 'No results found.'),
      'data-class-int-col' => self::CLASS_INT_COL,
      'data-template-columns' => static::getTemplateColumns($this->statisticModel),
    ] + $this->tableOptions;

    $this->showFooter = $this->dataProvider->getTotalCount() > 0;

    $this->beforeHeader = $this->getBeforeHeader();

    $this->registerJs();

    parent::init();
  }

  /**
   * Переход на стату по часам при клике на дату
   *
   * Скрытие периодов не подходящих под текущую группировку.
   * Для группировки по месяцам периоды Сегодня, Вчера и Неделя скрываются.
   * Для группировки по неделям периоды Сегодня и Вчера скрываются.
   *
   */
  private function registerJs()
  {
    /* TRICKY Колонки, по которым идет группировка отображаются в отдельной таблице слева от основной
     * Вырезать эти колонки из основной таблицы нельзя на yii,
     * поэтому эти колонки перемещаются в начало основной таблицы и вырезаются с помощью JS в файле statistic.js
     * fixedColumnsCount обозначает сколько колонок надо вырезать */
    $groupsCount = count($this->statisticModel->group);
    $this->view->registerJs(/** @lang JavaScript */
      "window.fixedColumnsCount = $groupsCount;",
      View::POS_HEAD);

    $this->getView()->registerJs(/** @lang JavaScript */'
      var $startDate = $("#statistic-start_date"),
          $endDate = $("#statistic-end_date");
      
      $(document).on("click", ".change_date", function (e) {
        e.preventDefault();
        $startDate.kvDatepicker("setDate", $(this).data("start") + "");
        $endDate.kvDatepicker("setDate", $(this).data("end") + "");
        if (!window.SETTING_AUTO_SUBMIT) {
          $("#statistic-filter-form").trigger("submit");
        }
      });
      
      updatePeriods = function () {
        var $this = $(this),
          groups = [],
          groupsArray = $(".statistic-group-filter").serializeArray(),
          hiddenPeriods = [],
          $periods = $("button[data-period]");
          
          $.each(groupsArray, function(key, value) {
            groups.push(value.value);
          });
      
        // Определение периодов для скрытия
        if ($.inArray("month_number", groups) > -1) hiddenPeriods = ["today", "yesterday", "week"];
        else if ($.inArray("week_number", groups) > -1) hiddenPeriods = ["today", "yesterday"];
      
        // Обновление списка периодов
        $periods.show(0).each(function (i, element) {
          var $element = $(element);
          if ($.inArray($element.data("period"), hiddenPeriods) > -1) $element.hide(0);
        });
      
        // Автоматический выбор первого доступного периода, если активный период был скрыт
        if ($periods.filter(".active:hidden").length > 0) {
          $periods.filter(":visible:first").trigger("click");
        }
      };
      
      $(".statistic-group-filter").on("change", updatePeriods);            
      updatePeriods();
    ');
  }

  public function getGraphicalStatisticData()
  {
    $result = [
      'quantity' => [
        'keys' => [],
        'labels' => [],
        'data' => [],
      ],
      'finance' => [
        'keys' => [],
        'labels' => [],
        'data' => [],
      ],
    ];

    $processedAttributes = [];

    /**
     * @var Statistic $item
     * @var DataColumn $column
     */

    foreach ($this->columns as $i => $column) {
      if (!isset(self::$gridColumns[$i]['format'])
        || in_array($column->attribute, $processedAttributes)
        || $column->attribute == '') {
        continue;
      }
      $processedAttributes[] = $column->attribute;

      if (self::$gridColumns[$i]['format'] === 'decimal') {
        $result['finance']['keys'][] = $column->key;
        $result['finance']['labels'][] = '['.self::getHeaderGroups(self::$gridColumns[$i]['groupType']).'] '.$column->label;
      } else if (self::$gridColumns[$i]['format'] === 'integer') {
        $result['quantity']['keys'][] = $column->key;
        $result['quantity']['labels'][] = '['.self::getHeaderGroups(self::$gridColumns[$i]['groupType']).'] '.$column->label;
      }
    }

  // Формирую массив дат в заданом диапазоне (чтобы избежать пропусков в графике)
    $start = strtotime($this->statisticModel->start_date);
    $finish = strtotime($this->statisticModel->end_date . ' 23:59:59');
    $arrayOfDates = [];
    $step = $this->statisticModel->isGroupingBy('date') ? 86400 : 3600;
    $groups = $this->statisticModel->group; // иначе current() ниже выкинет Notice, т.к. в него передается по ссылке
    for ($i = $start; $i <= $finish; $i += $step) {
      switch(current($groups)) {
        case 'date_hour':
          $arrayOfDates[date('Y-m-d_G', $i)] = [];
          break;
        case 'date':
          $arrayOfDates[date('Y-m-d', $i)] = [];
          break;
        case 'hour':
          $arrayOfDates[date('G', $i)] = [];
          break;
      }
    }
    $allModels = $this->dataProvider->allModels + $arrayOfDates;

    foreach ($allModels as $date => $model) {
      // для пропуска дубликатов
      $processedAttributes = [];
      $quantityData = [];
      $financeData = [];
      foreach ($this->columns as $i => $column) {
        if (!isset(self::$gridColumns[$i]['format'])
          || in_array($column->attribute, $processedAttributes)
          || $column->attribute == ''
        ) {
          continue;
        }

        $value = $column->getDataCellValue($model, $column->attribute, $date);

        $processedAttributes[] = $column->attribute;
        if (self::$gridColumns[$i]['format'] === 'decimal') {
          $financeData[$column->key] = (float)$value;
        } else if (self::$gridColumns[$i]['format'] === 'integer') {
          $quantityData[$column->key] = (int)$value;
        }
      }
      $period = $date;
      if ($this->statisticModel->isGroupingBy('date_hour')) {
        $dateTime = explode('_', $date);
        $period = $dateTime[0] . ' ' . $dateTime[1] . ':00';
      }

      $quantityData['period'] = $period;
      $financeData['period'] = $period;
      $result['quantity']['data'][] = $quantityData;
      $result['finance']['data'][] = $financeData;
    }

    return $result;
  }

  /**
   * @param Statistic $model
   * @param bool $cache Использовать кэш для результата.
   * TRICKY при добавилении нового столбца возможно нужно добавить его в системный шаблон
   * TRICKY Если в $model передается заглушка (например StatisticGrid::getGridColumns(new Statistic)),
   * то нужно обязательно передавать $cache = false, иначе готовьте водку и кучу времени на решение магических багов,
   * которые приведут сюда
   * @return array
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  public static function getGridColums($model, $cache = true)
  {
    if ($cache && !empty(self::$gridColumns)) {
      return self::$gridColumns;
    }

    $formatter = Yii::$app->formatter;

    $startDate = new DateTime($model->formatDateDB($model->start_date));
    $endDate = new DateTime($model->formatDateDB($model->end_date));
    /** @var bool $isMultiYear Статистика включает данные за разные года */
    $isMultiYear = $startDate->format('Y') != $endDate->format('Y');
    $canViewColumnsDecimals = Yii::$app->user->can(Module::VIEW_COLUMNS_DECIMALS);

    $groupColumns = [];
    $firstField = true;

    foreach ($model->group as $field) {
      $groupField = $model->groupFields[$field];
      $groupColumns[] = [
        'label' => $model->getGroupByFieldByGroup($field),
        'attribute' => $groupField,
        'format' => 'raw',
        'value' => function ($item) use ($model, $formatter, $isMultiYear, $field, $groupField) {
          if (!isset($item[$groupField])) return null; // Бывают пустые строки, без поля группы в том числе
          $value = is_array($item[$groupField]) ? current($item[$groupField]) : $item[$groupField];
          // По датам (делаем ссылку на стату по часам):
          if ($model->isGroupingByDate($field)) {
            return Html::tag('a',
              $formatter->asPartnerDate($value),
              [
                'href' => '#',
                'class' => 'change_date',
                'data-start' => $formatter->asDate($value, 'php:Y-m-d'),
                'data-end' => $formatter->asDate($value, 'php:Y-m-d'),
              ]
            );
          }

          // По месяцам или по неделям
          if ($model->isGroupingByMonth($field) || $model->isGroupingByWeek($field)) {
            $title = null;
            $weekOrMonth = explode('.', $item[$groupField])[1];

            // $item['date'] хранит случайную дату недели, так как данные сгрупированы
            $weekPeriod = $model->isGroupingByMonth($field)
              ? $model->getMonthPeriod($item['date'], $weekOrMonth)
              : $model->getWeekPeriod($item['date'], $weekOrMonth);
            $periodBegin = $weekPeriod[0]->format('d.m.Y');
            $periodEnd = $weekPeriod[1]->format('d.m.Y');
            $title = $periodBegin == $periodEnd ? $periodBegin : $periodBegin . ' - ' . $periodEnd;

            return Html::tag('div', $isMultiYear ? $item[$groupField] : $weekOrMonth, ['title' => $title]
            );
          }

          return $model->formatGroup($item, $field);
        },
        'footer' => $firstField ? Yii::_t('statistic.statistic_total') : null,
        'groupType' => self::GROUP,
        'contentOptions' => function ($item) use ($model, $field, $groupField) {
          if (!isset($item[$groupField])) return null; // Бывают пустые строки, без поля группы в том числе
          return [
            'data-sort' => $model->isGroupingByHour($field) ? mktime($item[$groupField]) : $item[$groupField],
          ];
        },
        'headerOptions' => [
          //'style' => 'height:87px;'
        ],
      ];

      $firstField = false;
    }

    $gridColumns = array_merge($groupColumns, [
      [
        'key' => 'count_hits',
        'attribute' => 'count_hits',
        'format' => 'integer',
        'groupType' => self::TRAFFIC,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => Yii::_t('statistic.statistic.traffic'),
          'data-code' => 'count_hits',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_hits'))
      ],
      [
        'key' => 'count_uniques',
        'attribute' => 'count_uniques',
        'format' => 'integer',
        'groupType' => self::TRAFFIC,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => Yii::_t('statistic.statistic.traffic'),
          'data-code' => 'count_uniques',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_uniques'))
      ],
      [
        'key' => 'count_tb',
        'attribute' => 'count_tb',
        'format' => 'integer',
        'groupType' => self::TRAFFIC,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.traffic'),
          'data-code' => 'count_tb',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_tb'))
      ],
      [
        'key' => 'accepted',
        'attribute' => 'accepted',
        'format' => 'integer',
        'value' => function ($item) use ($model) {
          return $model->getAcceptedValue($item);
        },
        'groupType' => self::TRAFFIC,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => Yii::_t('statistic.statistic.traffic'),
          'data-code' => 'accepted',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_accepted'))
      ],

      [
        'footerOptions' => ['id' => 'revshare_accepted_total'],
        'key' => 'revshare_accepted',
        'attribute' => 'revshare_accepted',
        'format' => 'integer',
        'value' => function ($item) use ($model) {
          return $model->getAcceptedValue($item, $model::REVSHARE);
        },
        'visible' => !$model->isCPA(),
        'visibleInTemplate' => true,
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'revshare_accepted',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('revshare_count_accepted'))
      ],
      [
        'footerOptions' => ['id' => 'count_ons_total'],
        'key' => 'count_ons',
        'attribute' => 'count_ons',
        'format' => 'integer',
        'visible' => !$model->isCPA(),
        'visibleInTemplate' => true,
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'count_ons',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_ons'))
      ],
      [
        'footerOptions' => ['id' => 'count_scope_offs_total'],
        'attribute' => 'count_scope_offs',
        'value' => function ($item) use ($model, $formatter) {
          $subs = ArrayHelper::getValue($item, 'count_ons', 0);
          $subsOffs = ArrayHelper::getValue($item, 'count_scope_offs', 0);

          return $formatter->asInteger($subsOffs) . ' (' . $formatter->asPercent([$subsOffs, $subs], 2) .')';
        },
        'visible' => !$model->isCPA(),
        'visibleInTemplate' => true,
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'count_scope_offs',
        ],
        'contentOptions' => function ($item) {
          return [
            'data-sort' => ArrayHelper::getValue($item, 'count_scope_offs', 0)
          ];
        },
        'footer' => $formatter->asInteger($model->getResultValue('count_scope_offs'))
        . ' ('. $formatter->asPercent([$model->getResultValue('count_scope_offs'), $model->getResultValue('count_ons')], 2).')',
      ],
      [
        'footerOptions' => ['id' => 'revshare_ratio_total'],
        'attribute' => 'revshare_ratio',
        'value' => function ($item) use ($model) {
          return $model->getRevshareRatio($item, '1:%s (%s)', true);
        },
        'visible' => !$model->isCPA(),
        'visibleInTemplate' => true,
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'revshare_ratio',
        ],
        'footer' => $formatter->asRatio($model->getResultValue('revshare_ratio'), $model->getResultValue('cr_revshare_ratio'), true),
        'contentOptions' => function ($item) use ($model) {
          return [
            'data-sort' => $model->getRevshareRatio($item, '%s'),
          ];
        },
      ],
      [
        'key' => 'count_offs',
        'attribute' => 'count_offs',
        'format' => 'integer',
        'visible' => !$model->isCPA(),
        'visibleInTemplate' => true,
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'count_offs',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_offs'))
      ],
      [
        'key' => 'count_longs',
        'attribute' => 'count_longs',
        'format' => 'integer',
        'visible' => !$model->isCPA(),
        'visibleInTemplate' => true,
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'count_longs',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_longs'))
      ],
      [
        'key' => 'charges_on_date',
        'label' => Yii::_t('statistic.statistic.charges_on_date'),
        'format' => 'integer',
        'visible' => !$model->isCPA() && $model->canViewAdditionalStatistic(),
        'visibleInTemplate' => $model->canViewAdditionalStatistic(),
        'attribute' => 'count_longs_date_by_date',
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => 'charges_on_date',
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'charges_on_date',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_longs_date_by_date')),
      ],
      [
        'key' => 'charge_ratio',
        'label' => Yii::_t('statistic.statistic.charge_ratio'),
        'format' => ['decimal', '4'],
        'visible' => !$model->isCPA() && $model->canViewAdditionalStatistic(),
        'visibleInTemplate' => $model->canViewAdditionalStatistic(),
        'value' => function ($item) use ($model) {
          return $model->getChargeRatio($item);
        },
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => 'charge_ratio',
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'charge_ratio',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('charge_ratio')),
      ],
      [
        'key' => 'sum_on_date_rub',
        'label' => Yii::_t('statistic.statistic.sum_on_date'),
        'format' => $canViewColumnsDecimals ? ['decimal', 4] : ['decimal', '2'],
        'visible' => !$model->isCPA() && $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('rub'),
        'visibleInTemplate' => $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('rub'),
        'attribute' => 'sum_profit_rub_date_by_date',
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => 'sum_on_date_rub',
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'sum_on_date',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sum_profit_rub_date_by_date')),
      ],
      [
        'key' => 'sum_on_date_usd',
        'label' => Yii::_t('statistic.statistic.sum_on_date'),
        'format' => $canViewColumnsDecimals ? ['decimal', 4] : ['decimal', '2'],
        'visible' => !$model->isCPA() && $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('usd'),
        'visibleInTemplate' => $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('usd'),
        'attribute' => 'sum_profit_usd_date_by_date',
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => 'sum_on_date_usd',
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'sum_on_date',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sum_profit_usd_date_by_date')),
      ],
      [
        'key' => 'sum_on_date_eur',
        'label' => Yii::_t('statistic.statistic.sum_on_date'),
        'format' => $canViewColumnsDecimals ? ['decimal', 4] : ['decimal', '2'],
        'visible' => !$model->isCPA() && $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('eur'),
        'visibleInTemplate' => $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('eur'),
        'attribute' => 'sum_profit_eur_date_by_date',
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => 'sum_on_date_eur',
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'sum_on_date',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sum_profit_eur_date_by_date')),
      ],
      [
        'key' => 'rev_sub_rub',
        'label' => Yii::_t('statistic.statistic.rev_sub'),
        'format' => ['decimal', '4'],
        'visible' => !$model->isCPA() && $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('rub'),
        'visibleInTemplate' => $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('rub'),
        'value' => function ($item) use ($model) {
          return $model->getRevSub($item, 'rub');
        },
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => 'rev_sub_rub',
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'rev_sub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('rev_sub_rub')),
      ],
      [
        'key' => 'rev_sub_usd',
        'label' => Yii::_t('statistic.statistic.rev_sub'),
        'format' => ['decimal', '4'],
        'visible' => !$model->isCPA() && $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('usd'),
        'visibleInTemplate' => $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('usd'),
        'value' => function ($item) use ($model) {
          return $model->getRevSub($item, 'usd');
        },
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => 'rev_sub_usd',
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'rev_sub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('rev_sub_usd')),
      ],
      [
        'key' => 'rev_sub_eur',
        'label' => Yii::_t('statistic.statistic.rev_sub'),
        'format' => ['decimal', '4'],
        'visible' => !$model->isCPA() && $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('eur'),
        'visibleInTemplate' => $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('eur'),
        'value' => function ($item) use ($model) {
          return $model->getRevSub($item, 'eur');
        },
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => 'rev_sub_eur',
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'rev_sub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('rev_sub_eur')),
      ],
      [
        'key' => 'roi_on_date_rub',
        'label' => Yii::_t('statistic.statistic.roi_on_date'),
        'format' => $canViewColumnsDecimals ? ['decimal', 4] : ['decimal', '2'],
        'visible' => !$model->isCPA() && $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('rub'),
        'visibleInTemplate' => $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('rub'),
        'value' => function ($item) use ($model) {
          return $model->getRoiOnDate($item, 'rub');
        },
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => 'roi_on_date_rub',
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'roi_on_date',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('roi_on_date_rub')),
      ],
      [
        'key' => 'roi_on_date_usd',
        'label' => Yii::_t('statistic.statistic.roi_on_date'),
        'format' => $canViewColumnsDecimals ? ['decimal', 4] : ['decimal', '2'],
        'visible' => !$model->isCPA() && $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('usd'),
        'visibleInTemplate' => $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('usd'),
        'value' => function ($item) use ($model) {
          return $model->getRoiOnDate($item, 'usd');
        },
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => 'roi_on_date_usd',
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'roi_on_date',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('roi_on_date_usd')),
      ],
      [
        'key' => 'roi_on_date_eur',
        'label' => Yii::_t('statistic.statistic.roi_on_date'),
        'format' => $canViewColumnsDecimals ? ['decimal', 4] : ['decimal', '2'],
        'visible' => !$model->isCPA() && $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('eur'),
        'visibleInTemplate' => $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency('eur'),
        'value' => function ($item) use ($model) {
          return $model->getRoiOnDate($item, 'eur');
        },
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => 'roi_on_date_eur',
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'roi_on_date',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('roi_on_date_eur')),
      ],
      [
        'key' => 'sum_real_profit',
        'attribute' => 'sum_real_profit_rub',
        'format' => 'decimal',
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('rub') && !$model->isCPA(),
        'visibleInTemplate' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('rub'),
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'sum_real_profit_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sum_real_profit_rub'))
      ],
      [
        'key' => 'sum_real_profit',
        'attribute' => 'sum_real_profit_eur',
        'format' => 'decimal',
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('eur') && !$model->isCPA(),
        'visibleInTemplate' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('eur'),
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'sum_real_profit_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sum_real_profit_eur'))
      ],
      [
        'key' => 'sum_real_profit',
        'attribute' => 'sum_real_profit_usd',
        'format' => 'decimal',
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('usd') && !$model->isCPA(),
        'visibleInTemplate' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('usd'),
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'sum_real_profit_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sum_real_profit_usd'))
      ],
      [
        'key' => 'sum_reseller_profit',
        'attribute' => 'sum_reseller_profit_rub',
        'format' => 'decimal',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('rub') && !$model->isCPA(),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('rub'),
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'sum_reseller_profit_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sum_reseller_profit_rub'))
      ],
      [
        'key' => 'sum_reseller_profit',
        'attribute' => 'sum_reseller_profit_eur',
        'format' => 'decimal',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('eur') && !$model->isCPA(),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('eur'),
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'sum_reseller_profit_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sum_reseller_profit_eur'))
      ],
      [
        'key' => 'sum_reseller_profit',
        'attribute' => 'sum_reseller_profit_usd',
        'format' => 'decimal',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('usd') && !$model->isCPA(),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('usd'),
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'sum_reseller_profit_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sum_reseller_profit_usd'))
      ],
      [
        'key' => 'sum_profit',
        'attribute' => 'sum_profit_rub',
        'format' => 'decimal',
        'label' => Yii::_t('statistic.statistic.sum_profit'),
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub') && !$model->isCPA(),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub'),
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'sum_profit_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sum_profit_rub'))
      ],
      [
        'key' => 'sum_profit',
        'attribute' => 'sum_profit_usd',
        'format' => 'decimal',
        'label' => Yii::_t('statistic.statistic.sum_profit'),
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd') && !$model->isCPA(),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd'),
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'sum_profit_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sum_profit_usd'))
      ],
      [
        'key' => 'sum_profit',
        'attribute' => 'sum_profit_eur',
        'format' => 'decimal',
        'label' => Yii::_t('statistic.statistic.sum_profit'),
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur') && !$model->isCPA(),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur'),
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => Yii::_t('statistic.statistic.revshare'),
          'data-code' => 'sum_profit_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sum_profit_eur'))
      ],


      [
        'key' => 'cpa_accepted',
        'attribute' => 'cpa_accepted',
        'format' => 'integer',
        'value' => function ($item) use ($model) {
          return $model->getAcceptedValue($item, $model::CPA);
        },
        'visible' => !$model->isRevshare(),
        'visibleInTemplate' => true,
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'cpa_accepted',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('cpa_count_accepted'))
      ],
      [
        'key' => 'count_onetime',
        'attribute' => 'count_onetime',
        'format' => 'integer',
        'visible' => !$model->isRevshare(),
        'visibleInTemplate' => true,
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'count_onetime',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_onetime'))
      ],
      [
        'key' => 'count_sold',
        'attribute' => 'count_sold',
        'format' => 'integer',
        'visible' => !$model->isRevshare(),
        'visibleInTemplate' => true,
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'count_sold',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_sold'))
      ],
      [
        'label' => Yii::_t('statistic.statistic.cpa_ratio'),
        'key' => 'cpa_ratio',
        'value' => function ($item) use ($model) {
          return $model->getCPARatio($item, '1:%s (%s)', true);
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'class' => 'ratio ratio_cpa',
          'data-code' => 'cpa_ratio',
        ],
        'visible' => !$model->isRevshare(),
        'visibleInTemplate' => true,
        'footer' => $formatter->asRatio($model->getResultValue('cpa_ratio'), $model->getResultValue('cr_cpa_ratio'), true),
        'contentOptions' => function ($item) use ($model) {
          return [
            'data-sort' => $model->getCPARatio($item, '%s'),
          ];
        },
      ],

      [
        'key' => 'ecpm',
        'header' => Yii::_t('statistic.statistic.ecpm', ['currency' => 'RUB']),
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub'),
        'value' => function ($item) use ($model) {
          return $model->getECPM($item, 'rub');
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'class' => 'ecpm',
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'ecpm',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('cpa_ecpm_rub'))
      ],
      [
        'key' => 'ecpm',
        'label' => Yii::_t('statistic.statistic.ecpm', ['currency' => 'USD']),
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd'),
        'value' => function ($item) use ($model) {
          return $model->getECPM($item, 'usd');
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'class' => 'ecpm',
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'ecpm',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('cpa_ecpm_usd'))
      ],
      [
        'key' => 'ecpm',
        'header' => Yii::_t('statistic.statistic.ecpm', ['currency' => 'EUR']),
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur'),
        'value' => function ($item) use ($model) {
          return $model->getECPM($item, 'eur');
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'class' => 'ecpm',
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'ecpm',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('cpa_ecpm_eur'))
      ],

      [
        'key' => 'cpr',
        'header' => Yii::_t('statistic.statistic.cpr', ['currency' => 'RUB']),
        'format' => ['decimal', '3'],
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub'),
        'value' => function ($item) use ($model) {
          return $model->getCPR($item, 'rub');
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'class' => 'cpr',
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'cpr',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('cpr_rub'), 3)
      ],
      [
        'key' => 'cpr',
        'label' => Yii::_t('statistic.statistic.cpr', ['currency' => 'USD']),
        'format' => ['decimal', '3'],
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd'),
        'value' => function ($item) use ($model) {
          return $model->getCPR($item, 'usd');
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'class' => 'cpr',
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'cpr',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('cpr_usd'), 3)
      ],
      [
        'key' => 'cpr',
        'header' => Yii::_t('statistic.statistic.cpr', ['currency' => 'EUR']),
        'format' => ['decimal', '3'],
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur'),
        'value' => function ($item) use ($model) {
          return $model->getCPR($item, 'eur');
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'class' => 'cpr',
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'cpr',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('cpr_eur'), 3)
      ],
      [
        'key' => 'visible_subscriptions',
        'attribute' => 'visible_subscriptions',
        'value' => function ($item) use ($model) {
          return $model->getVisibleSubscriptions($item);
        },
        'label' => Yii::_t('statistic.statistic.visible_subscriptions'),
        'format' => 'integer',
        'visible' => $model->canViewVisibleSubscriptions() && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewVisibleSubscriptions(),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'visible_subscriptions',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('visible_subscriptions'))
      ],
      [
        'key' => 'onetime_real_profit',
        'attribute' => 'onetime_real_profit_rub',
        'format' => 'decimal',
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('rub') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('rub'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'onetime_real_profit_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('onetime_real_profit_rub'))
      ],
      [
        'key' => 'onetime_real_profit',
        'attribute' => 'onetime_real_profit_eur',
        'format' => 'decimal',
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('eur') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('eur'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'onetime_real_profit_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('onetime_real_profit_eur'))
      ],
      [
        'key' => 'onetime_real_profit',
        'attribute' => 'onetime_real_profit_usd',
        'format' => 'decimal',
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('usd') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('usd'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'onetime_real_profit_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('onetime_real_profit_usd'))
      ],
      [
        'key' => 'onetime_reseller_profit',
        'attribute' => 'onetime_reseller_profit_rub',
        'format' => 'decimal',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('rub') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('rub'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'onetime_reseller_profit_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('onetime_reseller_profit_rub'))
      ],
      [
        'key' => 'onetime_reseller_profit',
        'attribute' => 'onetime_reseller_profit_eur',
        'format' => 'decimal',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('eur') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('eur'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'onetime_reseller_profit_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('onetime_reseller_profit_eur'))
      ],
      [
        'key' => 'onetime_reseller_profit',
        'attribute' => 'onetime_reseller_profit_usd',
        'format' => 'decimal',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('usd') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('usd'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'onetime_reseller_profit_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('onetime_reseller_profit_usd'))
      ],
      [
        'key' => 'onetime_profit',
        'attribute' => 'onetime_profit_rub',
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'onetime_profit_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('onetime_profit_rub'))
      ],
      [
        'key' => 'onetime_profit',
        'attribute' => 'onetime_profit_usd',
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'onetime_profit_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('onetime_profit_usd'))
      ],
      [
        'key' => 'onetime_profit',
        'attribute' => 'onetime_profit_eur',
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'onetime_profit_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('onetime_profit_eur'))
      ],
      [
        'key' => 'sold_investor_price',
        'attribute' => 'sold_investor_price_rub',
        'format' => 'decimal',
        'visible' => $model->canViewSoldPrice() && $model->canViewColumnByCurrency('rub') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewSoldPrice() && $model->canViewColumnByCurrency('rub'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'sold_investor_price_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sold_investor_price_rub'))
      ],
      [
        'key' => 'sold_investor_price',
        'attribute' => 'sold_investor_price_eur',
        'format' => 'decimal',
        'visible' => $model->canViewSoldPrice() && $model->canViewColumnByCurrency('eur') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewSoldPrice() && $model->canViewColumnByCurrency('eur'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'sold_investor_price_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sold_investor_price_eur'))
      ],
      [
        'key' => 'sold_investor_price',
        'attribute' => 'sold_investor_price_usd',
        'format' => 'decimal',
        'visible' => $model->canViewSoldPrice() && $model->canViewColumnByCurrency('usd') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewSoldPrice() && $model->canViewColumnByCurrency('usd'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'sold_investor_price_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sold_investor_price_usd'))
      ],
      [
        'key' => 'sold_reseller_price',
        'attribute' => 'sold_reseller_price_rub',
        'format' => 'decimal',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('rub') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('rub'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'sold_reseller_price_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sold_reseller_price_rub'))
      ],
      [
        'key' => 'sold_reseller_price',
        'attribute' => 'sold_reseller_price_eur',
        'format' => 'decimal',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('eur') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('eur'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'sold_reseller_price_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sold_reseller_price_eur'))
      ],
      [
        'key' => 'sold_reseller_price',
        'attribute' => 'sold_reseller_price_usd',
        'format' => 'decimal',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('usd') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('usd'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'sold_reseller_price_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sold_reseller_price_usd'))
      ],
      [
        'key' => 'sold_partner_profit',
        'attribute' => 'sold_partner_profit_rub',
        'label' => $model->getGridColumnLabel('sold_price_rub'),
        'format' => 'decimal',
        'visible' => $model->canViewColumnByCurrency('rub') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewColumnByCurrency('rub'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'sold_partner_profit_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sold_partner_profit_rub'))
      ],
      [
        'key' => 'sold_partner_profit',
        'attribute' => 'sold_partner_profit_usd',
        'label' => $model->getGridColumnLabel('sold_price_usd'),
        'format' => 'decimal',
        'visible' => $model->canViewColumnByCurrency('usd') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewColumnByCurrency('usd'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'sold_partner_profit_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sold_partner_profit_usd'))
      ],
      [
        'key' => 'sold_partner_profit',
        'attribute' => 'sold_partner_profit_eur',
        'label' => $model->getGridColumnLabel('sold_price_eur'),
        'format' => 'decimal',
        'visible' => $model->canViewColumnByCurrency('eur') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewColumnByCurrency('eur'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'sold_partner_profit_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('sold_partner_profit_eur'))
      ],
      [
        'key' => 'investor_count_rebills',
        'attribute' => 'investor_count_rebills',
        'label' => $model->getGridColumnLabel('investor_count_rebills'),
        'format' => 'integer',
        'visible' => $model->canViewResellerProfit() && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewResellerProfit(),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'investor_count_rebills',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('investor_count_rebills'))
      ],
      [
        'key' => 'investor_profit',
        'attribute' => 'investor_profit_rub',
        'label' => $model->getGridColumnLabel('investor_profit'),
        'format' => 'decimal',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('rub') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('rub'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'investor_profit_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('investor_profit_rub'))
      ],
      [
        'key' => 'investor_profit',
        'attribute' => 'investor_profit_usd',
        'label' => $model->getGridColumnLabel('investor_profit'),
        'format' => 'decimal',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('usd') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('usd'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'investor_profit_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('investor_profit_usd'))
      ],
      [
        'key' => 'investor_profit',
        'attribute' => 'investor_profit_eur',
        'label' => $model->getGridColumnLabel('investor_profit'),
        'format' => 'decimal',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('eur') && !$model->isRevshare(),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('eur'),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => 'investor_profit_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('investor_profit_eur'))
      ],
    ]);

    // TOTALS
    $gridColumns += array_merge($gridColumns, [
      [
        'key' => 'admin_total_profit',
        'label' => $model->getGridColumnLabel('admin_total_profit_rub'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getTotalProfitAdmin($item, 'rub');
        },
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('rub'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'admin_total_profit_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('admin_total_profit_rub'))
      ],
      [
        'key' => 'admin_total_profit',
        'label' => $model->getGridColumnLabel('admin_total_profit_eur'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getTotalProfitAdmin($item, 'eur');
        },
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('eur'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'admin_total_profit_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('admin_total_profit_eur'))
      ],
      [
        'key' => 'admin_total_profit',
        'label' => $model->getGridColumnLabel('admin_total_profit_usd'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getTotalProfitAdmin($item, 'usd');
        },
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('usd'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'admin_total_profit_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('admin_total_profit_usd'))
      ],
      [
        'key' => 'admin_net_profit',
        'label' => $model->getGridColumnLabel('admin_net_profit_rub'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getNetProfitAdmin($item, 'rub');
        },
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('rub'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'admin_net_profit_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('admin_net_profit_rub'))
      ],
      [
        'key' => 'admin_net_profit',
        'label' => $model->getGridColumnLabel('admin_net_profit_eur'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getNetProfitAdmin($item, 'eur');
        },
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('eur'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'admin_net_profit_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('admin_net_profit_eur'))
      ],
      [
        'key' => 'admin_net_profit',
        'label' => $model->getGridColumnLabel('admin_net_profit_usd'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getNetProfitAdmin($item, 'usd');
        },
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('usd'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'admin_net_profit_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('admin_net_profit_usd'))
      ],
      [
        'key' => 'reseller_total_profit',
        'label' => $model->getGridColumnLabel('reseller_total_profit_rub'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getTotalProfitReseller($item, 'rub');
        },
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('rub'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'reseller_total_profit_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('reseller_total_profit_rub'))
      ],
      [
        'key' => 'reseller_total_profit',
        'label' => $model->getGridColumnLabel('reseller_total_profit_eur'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getTotalProfitReseller($item, 'eur');
        },
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('eur'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'reseller_total_profit_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('reseller_total_profit_eur'))
      ],
      [
        'key' => 'reseller_total_profit',
        'label' => $model->getGridColumnLabel('reseller_total_profit_usd'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getTotalProfitReseller($item, 'usd');
        },
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('usd'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'reseller_total_profit_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('reseller_total_profit_usd'))
      ],
      [
        'key' => 'reseller_net_profit',
        'label' => $model->getGridColumnLabel('reseller_net_profit_rub'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getNetProfitReseller($item, 'rub');
        },
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('rub'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'reseller_net_profit_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('reseller_net_profit_rub'))
      ],
      [
        'key' => 'reseller_net_profit',
        'label' => $model->getGridColumnLabel('reseller_net_profit_eur'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getNetProfitReseller($item, 'eur');
        },
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('eur'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'reseller_net_profit_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('reseller_net_profit_eur'))
      ],
      [
        'key' => 'reseller_net_profit',
        'label' => $model->getGridColumnLabel('reseller_net_profit_usd'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getNetProfitReseller($item, 'usd');
        },
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('usd'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'reseller_net_profit_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('reseller_net_profit_usd'))
      ],
      [
        'key' => 'partner_total_profit',
        'attribute' => 'partner_total_profit_rub',
        'label' => Yii::_t('statistic.statistic.partner_total_profit'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getTotalProfit($item, 'rub');
        },
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'partner_total_profit_rub',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('total_sum_rub'))
      ],
      [
        'key' => 'partner_total_profit',
        'attribute' => 'partner_total_profit_eur',
        'label' => Yii::_t('statistic.statistic.partner_total_profit'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getTotalProfit($item, 'eur');
        },
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'partner_total_profit_eur',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('total_sum_eur'))
      ],
      [
        'key' => 'partner_total_profit',
        'attribute' => 'partner_total_profit_usd',
        'label' => Yii::_t('statistic.statistic.partner_total_profit'),
        'format' => 'decimal',
        'value' => function ($item) use ($model) {
          return $model->getTotalProfit($item, 'usd');
        },
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd'),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'partner_total_profit_usd',
        ],
        'footer' => $formatter->asDecimal($model->getResultValue('total_sum_usd'))
      ],
      [
        'attribute' => 'count_complains',
        'label' => $model->getGridColumnLabel('count_complains'),
        'format' => 'raw',
        'visible' => $model->canViewComplainsStatistic(),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'count_complains',
        ],
        'value' => function ($item) use ($model) {

          $urlParams = $model->getRowFilterArray($item);

          $urlParams['type'] = Complain::TYPE_TEXT;

          $count = ArrayHelper::getValue($item, 'count_complains');
          return $count
            ? Html::a($count ?: 0,
              array_merge(['detail/complains'], ['statistic' => $urlParams]),
              ['data-pjax' => 0])
            : '0';
        },
        'contentOptions' => function ($item) {
          $count = ArrayHelper::getValue($item, 'count_complains');
          return [
            'data-sort' => $count ?: 0
          ];
        },
        'footer' => $formatter->asInteger($model->getResultValue('count_complains'))
      ],
      [
        'attribute' => 'count_calls',
        'label' => $model->getGridColumnLabel('count_calls'),
        'format' => 'raw',
        'visible' => $model->canViewComplainsStatistic(),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'count_calls',
        ],
        'value' => function ($item) use ($model) {

          $urlParams = $model->getRowFilterArray($item);

          $urlParams['type'] = Complain::TYPE_CALL;

          $count = ArrayHelper::getValue($item, 'count_calls');
          return $count
            ? Html::a($count ?: 0,
              array_merge(['detail/complains'], ['statistic' => $urlParams]),
              ['data-pjax' => 0])
            : '0';
        },
        'contentOptions' => function ($item) {
          $count = ArrayHelper::getValue($item, 'count_calls');
          return [
            'data-sort' => $count ?: 0
          ];
        },
        'footer' => $formatter->asInteger($model->getResultValue('count_calls'))
      ],
      [
        'attribute' => 'total_count_scope_offs',
        'label' => $model->getGridColumnLabel('total_count_scope_offs'),
        'format' => 'raw',
        'visible' => $model->canViewTotalCountScopeOffs(),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => 'total_count_scope_offs',
        ],
        'value' => function ($item) use ($model, $formatter) {
          $subs = ArrayHelper::getValue($item, 'count_ons', 0) + ArrayHelper::getValue($item, 'count_sold', 0);
          $subsOffs = ArrayHelper::getValue($item, 'count_scope_offs', 0) +
            ArrayHelper::getValue($item, 'investor_count_scope_offs', 0);

          return $formatter->asInteger($subsOffs) . ' (' . $formatter->asPercent([$subsOffs, $subs], 2) .')';
        },
        'contentOptions' => function ($item) {
          $count = ArrayHelper::getValue($item, 'count_scope_offs', 0) +
            ArrayHelper::getValue($item, 'investor_count_scope_offs', 0);
          return [
            'data-sort' => $count ?: 0
          ];
        },
        'footer' => $formatter->asInteger($model->getResultValue('count_scope_offs') + $model->getResultValue('investor_count_scope_offs'))
          . ' ('. $formatter->asPercent([
            ($model->getResultValue('count_scope_offs') + $model->getResultValue('investor_count_scope_offs')),
            ($model->getResultValue('count_ons') + $model->getResultValue('count_sold'))], 2).')',
      ],
    ]);

    foreach ($gridColumns as &$column) {
      $column['class'] = \mcms\statistic\components\grid\StatisticColumn::class;
    }

    if ($cache) self::$gridColumns = $gridColumns;

    return $gridColumns;
  }

  public function getBeforeHeader()
  {
    $groups = [];
    foreach (array_keys($this::getHeaderGroups()) as $key) {
      $groups[$key] = 0;
    }

    $header = [];

    // Группируемые колонки
    foreach($this->columns as $column) {
      $groupType = ArrayHelper::getValue($column, 'groupType');
      if (!($groupType) ||
        !ArrayHelper::getValue($column, 'visible', true)
      ) continue;

      if ($groupType === self::GROUP) {
        $header[] = Html::tag('th', $column['label'], ['rowspan' => 2]);
      } else {
        $groups[$groupType]++;
      }
    }

    // Категории данных
    foreach (array_filter($groups) as $groupKey => $count) {
      $header[] = Html::tag('td', self::getHeaderGroups($groupKey), ['colspan' => $count]);
    }

    return implode('', $header);
  }

  public static function getHeaderGroups($group = null)
  {
    $groups = [
      self::TRAFFIC => Yii::_t('statistic.statistic.traffic'),
      self::REVSHARE => Yii::_t('statistic.statistic.revshare'),
      self::CPA => Yii::_t('statistic.statistic.cpa'),
      self::TOTAL => Yii::_t('statistic.statistic.total_profit')
    ];
    return $group ? ArrayHelper::getValue($groups, $group) : $groups;
  }

  /**
   * Видимые столбцы для шаблона. Нужно иметь список всех возможных столбцов для данного юзера
   * из-за фильтра по revshare/cpa. Нельзя сделать через StatisticColumn, т.к. невидимые столбцы удаляются
   * @param \mcms\statistic\models\mysql\Statistic|null $model
   * @param string|null $systemTemplateId @see ColumnTemplate::QUANTITATIVE_STATISTICS
   * TRICKY В $model нужно передавать или настояющую модель, или null. Передавать заглушку в виде new Statistic нельзя,
   * иначе появятся проблемы описанные в методе @see getGridColumns()
   * @return array
   */
  public static function getTemplateColumns($model = null, $systemTemplateId = null) {
    // Вместо настоящей модели Statistic установлена заглушка
    $isModelStub = false;

    if (!$model) {
      $isModelStub = true;
      $model = new Statistic;
    }

    $templateColumns = [];
    foreach (self::getGridColums($model, !$isModelStub) as $column) {
      $isVisible = ArrayHelper::getValue($column, 'visibleInTemplate', ArrayHelper::getValue($column, 'visible', true));
      if ($isVisible) {
        $text = ArrayHelper::getValue($column, 'label');
        if (!$text) {
          $text = ArrayHelper::getValue($column, 'attribute') ? $model->getGridColumnLabel($column['attribute']) : ArrayHelper::getValue($column, 'header');
        }

        /* Замена валюты на паттерн.
        Сделано, что бы столбцы шаблона независили от валюты.
        Валюта подставляется в JS при выборе шаблона */
        $code = ArrayHelper::getValue($column, 'headerOptions.data-code');
        $code = str_replace(['rub', 'usd', 'eur'], '{currency}', $code);

        if ($systemTemplateId && !in_array($systemTemplateId, ArrayHelper::getValue($column, 'templates', []))) {
          continue;
        }

        $templateColumns[] = [
          'code' => $code,
          'text' => $text,
          'group' => ArrayHelper::getValue($column, 'groupType'),
          'groupLabel' => ArrayHelper::getValue($column, 'headerOptions.data-group'),
        ];
      }
    }

    return $templateColumns;
  }
}
