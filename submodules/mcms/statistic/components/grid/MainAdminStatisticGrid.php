<?php

namespace mcms\statistic\components\grid;

use DateTime;
use mcms\common\helpers\ArrayHelper;
use mcms\common\widget\AdminGridView;
use mcms\statistic\models\Complain;
use mcms\statistic\models\mysql\MainAdminStatistic;
use Yii;
use yii\grid\DataColumn;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

/**
 * Грид для основной статы админки. Работает с серч-моделью @see MainAdminStatistic, но в датапровайдере массивы
 * (на момент написания этого коммента по крайней мере)
 */
class MainAdminStatisticGrid extends AdminGridView
{

  const TRAFFIC = 'traffic';
  const REVSHARE = 'revshare';
  const CPA = 'cpa';
  const TOTAL = 'total';
  const EFFICIENCY = 'efficiency';
  const ONETIME = 'onetime';
  const COMPLAINS = 'complains';
  const SELL_TB = 'sellTb';

  const GROUP = 'group';

  /**
   * Класс, который нужно подставить в столбцы с числами, чтобы сортировка велась корректно
   */
  const CLASS_INT_COL = 'datatable-int-col';

  public $resizableColumns = false;

  public $layout = '{items}';

  public $export = false;
  public $condensed = true;
  public $responsiveWrap = false;
  public $emptyCell = '';
  public $template;

  /**
   * @var MainAdminStatistic
   */
  public $statisticModel;
  /** @var  StatisticColumn[] Все колонки грида в виде объектов класса. Нужен по причине того,
   * что в @see StatisticColumns::$columns лежат только видимые колонки (особенность YiiGridView)
   * А для того чтоб отобразить подсказки, нам нужны переводы всех столбцов,
   * иначе те которые скрытые мы не сможем использовать и подставить в подсказку.
   */
  public $allColumns;

  public $dataColumnClass = 'mcms\statistic\components\grid\StatisticColumn';

  /**
   * @var array
   */
  private static $gridColumns = [];

  public function init()
  {
    $this->columns = self::getGridColums($this->statisticModel, true, $this->template);
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
        var groups = [],
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

  /**
   * @param MainAdminStatistic $model
   * @param bool $cache Использовать кэш для результата.
   * @param int|null $templateId id шаблона грида статистики
   * TRICKY При добавлении нового столбца нужно добавить его в соответствующий системный шаблон
   * TRICKY Если в $model передается заглушка (например StatisticGrid::getGridColumns(new Statistic)),
   * то нужно обязательно передавать $cache = false, иначе готовьте водку и кучу времени на решение магических багов,
   * которые приведут сюда
   * @return array
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  public static function getGridColums($model, $cache = true, $templateId = null)
  {
    if ($cache && !empty(self::$gridColumns[$templateId])) {
      return self::$gridColumns[$templateId];
    }

    $formatter = Yii::$app->formatter;
    $startDate = new DateTime($model->formatDateDB($model->start_date));
    $endDate = new DateTime($model->formatDateDB($model->end_date));
    /** @var bool $isMultiYear Статистика включает данные за разные года */
    $isMultiYear = $startDate->format('Y') != $endDate->format('Y');
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
          'data-group' => self::getHeaderGroups(self::TRAFFIC),
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
          'data-group' => self::getHeaderGroups(self::TRAFFIC),
          'data-code' => 'count_uniques',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_uniques'))
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
          'data-group' => self::getHeaderGroups(self::TRAFFIC),
          'data-code' => 'accepted',
        ],
        'hint' => '{{count_hits}} - {{count_tb}}',
        'footer' => $formatter->asInteger($model->getResultValue('count_accepted'))
      ],
      [
        'key' => 'count_tb',
        'attribute' => 'count_tb',
        'format' => 'integer',
        'groupType' => self::TRAFFIC,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::TRAFFIC),
          'data-code' => 'count_tb',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_tb'))
      ],

      [
        'attribute' => 'ecpc',
        'value' => function ($item) use ($model) {
          return $model->getEcpc($item, $model->currency);
        },
        'format' => ['decimal', 5],
        'groupType' => self::EFFICIENCY,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::EFFICIENCY),
          'data-code' => 'ecpc',
        ],
        'hint' => '{{partner_total_profit}} / {{count_hits}}',
        'footer' => $formatter->asStatisticSum($model->getResultValue("ecpc_{$model->currency}"), 5)
      ],

      [
        'footerOptions' => ['id' => 'revshare_accepted_total'],
        'key' => 'revshare_accepted',
        'attribute' => 'revshare_accepted',
        'format' => 'integer',
        'value' => function ($item) use ($model) {
          return $model->getAcceptedValue($item, $model::REVSHARE);
        },
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => self::getHeaderGroups(self::REVSHARE),
          'data-code' => 'revshare_accepted',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('revshare_count_accepted'))
      ],
      [
        'footerOptions' => ['id' => 'count_ons_total'],
        'key' => 'count_ons',
        'attribute' => 'count_ons',
        'format' => 'integer',
        'visibleInTemplate' => true,
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => self::getHeaderGroups(self::REVSHARE),
          'data-code' => 'count_ons',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_ons'))
      ],
      [
        'key' => 'count_offs',
        'attribute' => 'count_offs',
        'format' => 'integer',
        'visibleInTemplate' => true,
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => self::getHeaderGroups(self::REVSHARE),
          'data-code' => 'count_offs',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_offs'))
      ],
      [
        'footerOptions' => ['id' => 'count_scope_offs_total'],
        'attribute' => 'count_scope_offs',
        'value' => function ($item) use ($model, $formatter) {
          $subs = ArrayHelper::getValue($item, 'count_ons', 0);
          $subsOffs = ArrayHelper::getValue($item, 'count_scope_offs', 0);

          return $formatter->asInteger($subsOffs) . ' (' . $formatter->asPercent([$subsOffs, $subs], 2) .')';
        },
        'visibleInTemplate' => true,
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::REVSHARE),
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
          return $model->getRevshareRatio($item, '1:%s');
        },
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::REVSHARE),
          'data-code' => 'revshare_ratio',
        ],
        'footer' => $formatter->asRatio($model->getResultValue('revshare_ratio')),
        'hint' => '{{revshare_accepted}} / {{count_ons}}',
        'contentOptions' => function ($item) use ($model) {
          return [
            'data-sort' => $model->getRevshareRatio($item, '%s'),
          ];
        },
      ],
      [
        'footerOptions' => ['id' => 'revshare_cr_total'],
        'attribute' => 'revshare_cr',
        'format' => 'statisticSum',
        'value' => function ($item) use ($model) {
          return $model->getRevshareCr($item);
        },
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::REVSHARE),
          'data-code' => 'revshare_cr',
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue('cr_revshare_ratio')),
        'hint' => '{{count_ons}} / {{revshare_accepted}} * 100',
        'contentOptions' => function ($item) use ($model) {
          return [
            'data-sort' => $model->getRevshareCr($item),
          ];
        },
      ],

      [
        'key' => 'charges_on_date',
        'format' => 'integer',
        'visible' => $model->canViewAdditionalStatistic(),
        'visibleInTemplate' => $model->canViewAdditionalStatistic(),
        'attribute' => 'count_rebills_date_by_date',
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => 'charges_on_date',
          'data-group' => self::getHeaderGroups(self::REVSHARE),
          'data-code' => 'charges_on_date',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_rebills_date_by_date')),
      ],
      [
        'key' => 'charge_ratio',
        'attribute' => 'charge_ratio',
        'format' => ['percent', '2'],
        'visible' => $model->canViewAdditionalStatistic(),
        'visibleInTemplate' => $model->canViewAdditionalStatistic(),
        'value' => function ($item) use ($model) {
          return $model->getChargeRatio($item);
        },
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => 'charge_ratio',
          'data-group' => self::getHeaderGroups(self::REVSHARE),
          'data-code' => 'charge_ratio',
        ],
        'hint' => '{{charges_on_date}} / {{count_ons}} * 100',
        'footer' => $formatter->asPercent($model->getResultValue('charge_ratio'), 2),
      ],
      [
        'key' => "sum_on_date_{$model->currency}",
        'label' => Yii::_t('statistic.statistic.sum_on_date'),
        'format' => 'statisticSum',
        'visible' => $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency($model->currency),
        'visibleInTemplate' => $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency($model->currency),
        'attribute' => "sum_profit_{$model->currency}_date_by_date",
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => "sum_on_date_{$model->currency}",
          'data-group' => self::getHeaderGroups(self::REVSHARE),
          'data-code' => 'sum_on_date',
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue("sum_profit_{$model->currency}_date_by_date")),
      ],
      [
        'key' => 'count_rebills',
        'attribute' => 'count_rebills',
        'format' => 'integer',
        'visibleInTemplate' => true,
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => self::getHeaderGroups(self::REVSHARE),
          'data-code' => 'count_rebills',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_rebills'))
      ],
      [
        'key' => 'sum_reseller_profit',
        'label' => Yii::_t('statistic.main_statistic.sum_reseller_profit'),
        'attribute' => "sum_reseller_profit_{$model->currency}",
        'format' => 'statisticSum',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::REVSHARE),
          'data-code' => "sum_reseller_profit_{$model->currency}",
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue("sum_reseller_profit_{$model->currency}"))
      ],
      [
        'attribute' => "revshare_reseller_net_profit_{$model->currency}",
        'label' => $model->getGridColumnLabel('reseller_net_profit'),
        'format' => 'statisticSum',
        'value' => function ($item) use ($model) {
          return $model->getRevshareResellerNetProfit($item, $model->currency);
        },
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => self::getHeaderGroups(self::REVSHARE),
          'data-code' => "revshare_reseller_net_profit_{$model->currency}",
        ],
        'hint' => '{{sum_reseller_profit}} - {{sum_profit}}',
        'footer' => $formatter->asStatisticSum($model->getResultValue("revshare_reseller_net_profit_{$model->currency}"))
      ],
      [
        'key' => 'sum_profit',
        'label' => Yii::_t('statistic.main_statistic.sum_profit'),
        'attribute' => "sum_profit_{$model->currency}",
        'format' => 'statisticSum',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency($model->currency),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::REVSHARE),
          'data-code' => "sum_profit_{$model->currency}",
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue("sum_profit_{$model->currency}"))
      ],

      [
        'attribute' => 'revshare_ecpc',
        'value' => function ($item) use ($model) {
          return $model->getEcpcRevshare($item, $model->currency);
        },
        'format' => ['decimal', 5],
        'groupType' => self::REVSHARE,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::REVSHARE),
          'data-code' => 'revshare_ecpc',
        ],
        'hint' => '{{sum_profit}} / {{revshare_accepted}}',
        'footer' => $formatter->asDecimal($model->getResultValue("revshare_ecpc_{$model->currency}"), 5)
      ],

      [
        'key' => 'cpa_accepted',
        'attribute' => 'cpa_accepted',
        'format' => 'integer',
        'value' => function ($item) use ($model) {
          return $model->getAcceptedValue($item, $model::CPA);
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'cpa_accepted',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('cpa_count_accepted'))
      ],
      [
        'key' => 'count_cpa_ons',
        'value' => function ($item) use ($model) {
          return $model->getCountCpaOns($item);
        },
        'attribute' => 'count_cpa_ons',
        'format' => 'integer',
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'count_cpa_ons',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_cpa_ons'))
      ],
      [
        'key' => 'count_sold',
        'attribute' => 'count_sold',
        'format' => 'integer',
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'count_sold',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_sold'))
      ],
      [
        'key' => 'count_not_sold',
        'attribute' => 'cpa_rejected_count_ons',
        'format' => 'integer',
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'count_not_sold',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('cpa_rejected_count_ons'))
      ],
      [
        'key' => 'visible_subscriptions',
        'attribute' => 'visible_subscriptions',
        'value' => function ($item) use ($model) {
          return $model->getVisibleSubscriptions($item);
        },
        'format' => 'integer',
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'visible_subscriptions',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('visible_subscriptions'))
      ],
      [
        'key' => 'cpa_count_offs',
        'attribute' => 'cpa_count_offs',
        'format' => 'integer',
        'value' => function ($item) use ($model) {
          return $model->getCountCpaOffs($item);
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'cpa_count_offs',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_cpa_offs'))
      ],
      [
        'footerOptions' => ['id' => 'cpa_count_scope_offs'],
        'attribute' => 'cpa_count_scope_offs',
        'value' => function ($item) use ($model, $formatter) {
          $subs = $model->getCountCpaOns($item);
          $subsOffs = $model->getCpaOff24($item);

          return $formatter->asInteger($subsOffs) . ' (' . $formatter->asPercent([$subsOffs, $subs], 2) .')';
        },
        'visibleInTemplate' => true,
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'cpa_count_scope_offs',
        ],
        'contentOptions' => function ($item) {
          return [
            'data-sort' => ArrayHelper::getValue($item, 'cpa_count_scope_offs', 0)
          ];
        },
        'footer' => $formatter->asInteger($model->getResultValue('cpa_count_scope_offs'))
          . ' ('. $formatter->asPercent([$model->getResultValue('cpa_count_scope_offs'), $model->getResultValue('count_cpa_ons')], 2).')',
      ],
      [
        'footerOptions' => ['id' => 'cpa_ratio_total'],
        'attribute' => 'cpa_ratio',
        'value' => function ($item) use ($model) {
          return $model->getCpaRatio($item, '1:%s');
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'cpa_ratio',
        ],
        'footer' => $formatter->asRatio($model->getResultValue('cpa_ratio')),
        'hint' => '{{cpa_accepted}} / {{count_cpa_ons}}',
        'contentOptions' => function ($item) use ($model) {
          return [
            'data-sort' => $model->getCpaRatio($item, '%s'),
          ];
        },
      ],
      [
        'footerOptions' => ['id' => 'cpa_cr_total'],
        'attribute' => 'cpa_cr',
        'format' => 'statisticSum',
        'value' => function ($item) use ($model) {
          return $model->getCpaCr($item);
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'cpa_cr',
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue('cr_cpa_ratio')),
        'hint' => '{{count_cpa_ons}} / {{cpa_accepted}} * 100',
        'contentOptions' => function ($item) use ($model) {
          return [
            'data-sort' => $model->getCpaCr($item),
          ];
        },
      ],
      [
        'footerOptions' => ['id' => 'cpa_cr_sold'],
        'attribute' => 'cpa_cr_sold',
        'format' => 'statisticSum',
        'value' => function ($item) use ($model) {
          return $model->getCpaCrSold($item);
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'cpa_cr_sold',
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue('cpa_cr_sold')),
        'hint' => '{{count_sold}} / {{cpa_accepted}} * 100',
        'contentOptions' => function ($item) use ($model) {
          return [
            'data-sort' => $model->getCpaCrSold($item),
          ];
        },
      ],
      [
        'attribute' => 'partner_visible_cpa_cr',
        'format' => 'statisticSum',
        'value' => function ($item) use ($model) {
          return $model->getCpaCr($item, true);
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'partner_visible_cpa_cr',
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue('cr_partner_visible_cpa_ratio')),
        'hint' => '{{visible_subscriptions}} / {{cpa_accepted}} * 100',
        'contentOptions' => function ($item) use ($model) {
          return [
            'data-sort' => $model->getCpaCr($item, true),
          ];
        },
      ],
      [
        'key' => 'cpa_charges_on_date',
        'format' => 'integer',
        'visible' => $model->canViewAdditionalStatistic(),
        'visibleInTemplate' => $model->canViewAdditionalStatistic(),
        'attribute' => 'cpa_count_rebills_date_by_date',
        'value' => function ($item) use ($model) {
          return $model->getCpaRebillsDateByDate($item);
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'class' => 'cpa_charges_on_date',
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'cpa_charges_on_date',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('cpa_count_rebills_date_by_date')),
      ],
      [
        'key' => 'cpa_charge_ratio',
        'attribute' => 'cpa_charge_ratio',
        'format' => ['percent', '2'],
        'visible' => $model->canViewAdditionalStatistic(),
        'visibleInTemplate' => $model->canViewAdditionalStatistic(),
        'value' => function ($item) use ($model) {
          return $model->getCpaChargeRatio($item);
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'class' => 'cpa_charge_ratio',
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'cpa_charge_ratio',
        ],
        'hint' => '{{cpa_charges_on_date}} / {{count_cpa_ons}} * 100',
        'footer' => $formatter->asPercent($model->getResultValue('cpa_charge_ratio'), 2),
      ],
      [
        'key' => "cpa_sum_on_date_{$model->currency}",
        'label' => Yii::_t('statistic.statistic.sum_on_date'),
        'format' => 'statisticSum',
        'visible' => $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency($model->currency),
        'visibleInTemplate' => $model->canViewAdditionalStatistic() && $model->canViewColumnByCurrency($model->currency),
        'attribute' => "cpa_sum_profit_{$model->currency}_date_by_date",
        'value' => function ($item) use ($model) {
          return $model->getCpaSumOnDate($item, $model->currency);
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'class' => "cpa_sum_on_date_{$model->currency}",
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'cpa_sum_on_date',
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue("cpa_sum_profit_date_by_date_{$model->currency}")),
      ],
      [
        'key' => 'cpa_count_rebills',
        'attribute' => 'cpa_count_rebills',
        'format' => 'integer',
        'visibleInTemplate' => true,
        'groupType' => self::CPA,
        'value' => function ($item) use ($model) {
          return $model->getCpaRebills($item);
        },
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'cpa_count_rebills',
        ],
        'hint' => '{{cpa_count_rebills_sold}} + {{cpa_rejected_count_rebills}}',
        'footer' => $formatter->asInteger($model->getResultValue('cpa_count_rebills'))
      ],
      [
        'key' => 'cpa_count_rebills_sold',
        'attribute' => 'cpa_count_rebills_sold',
        'format' => 'integer',
        'visibleInTemplate' => true,
        'groupType' => self::CPA,
        'value' => function ($item) use ($model) {
          return $model->getSoldRebills($item);
        },
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'cpa_count_rebills_sold',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('cpa_count_rebills_sold'))
      ],
      [
        'key' => 'cpa_rejected_count_rebills',
        'attribute' => 'cpa_rejected_count_rebills',
        'format' => 'integer',
        'visibleInTemplate' => true,
        'groupType' => self::CPA,
        'value' => function ($item) use ($model) {
          return $model->getRejectedRebills($item);
        },
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'cpa_rejected_count_rebills',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('cpa_rejected_count_rebills'))
      ],
      [
        'key' => 'cpa_profit',
        'label' => $model->getGridColumnLabel('cpa_profit'),
        'format' => 'statisticSum',
        'value' => function ($item) use ($model) {
          return $model->getCpaProfit($item, $model->currency);
        },
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => "cpa_profit_{$model->currency}",
        ],
        'hint' => '{{partner_sold_profit}} + {{rejected_cpa_profit}}',
        'footer' => $formatter->asStatisticSum($model->getResultValue("cpa_profit_{$model->currency}"))
      ],
      [
        'key' => 'partner_sold_profit',
        'attribute' => "sold_cpa_profit_{$model->currency}",
        'label' => $model->getGridColumnLabel('partner_sold_profit'),
        'format' => 'statisticSum',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => "sold_cpa_profit_{$model->currency}",
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue("sold_cpa_profit_{$model->currency}"))
      ],
      [
        'key' => 'rejected_cpa_profit',
        'attribute' => "rejected_cpa_profit_{$model->currency}",
        'label' => $model->getGridColumnLabel('rejected_cpa_profit'),
        'format' => 'statisticSum',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.cpa'),
          'data-code' => "rejected_cpa_profit_{$model->currency}",
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue("rejected_cpa_profit_{$model->currency}"))
      ],
      [
        'attribute' => "cpa_reseller_net_profit_{$model->currency}",
        'label' => $model->getGridColumnLabel('reseller_net_profit'),
        'format' => 'statisticSum',
        'value' => function ($item) use ($model) {
          return $model->getCpaResellerNetProfit($item, $model->currency);
        },
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::CPA,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => "cpa_reseller_net_profit_{$model->currency}",
        ],
        'hint' => '{{partner_sold_profit}} - {{sold_partner_profit}}',
        'footer' => $formatter->asStatisticSum($model->getResultValue("cpa_reseller_net_profit_{$model->currency}"))
      ],
      [
        'key' => 'sold_partner_profit',
        'attribute' => "sold_partner_profit_{$model->currency}",
        'label' => $model->getGridColumnLabel('sold_partner_profit'),
        'format' => 'statisticSum',
        'visible' => $model->canViewColumnByCurrency($model->currency),
        'visibleInTemplate' => $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => "sold_partner_profit_{$model->currency}",
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue("sold_partner_profit_{$model->currency}"))
      ],

      [
        'attribute' => 'ecp',
        'value' => function ($item) use ($model) {
          return $model->getEcp($item, $model->currency);
        },
        'format' => ['decimal', 5],
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'ecp',
        ],
        'hint' => Yii::_t('statistic.main_statistic.sold_partner_price') . ' / {{count_hits}}',
        'footer' => $formatter->asDecimal($model->getResultValue("ecp_{$model->currency}"), 5)
      ],

      [
        'attribute' => 'cpa_ecpc',
        'value' => function ($item) use ($model) {
          return $model->getEcpcCpa($item, $model->currency);
        },
        'format' => ['decimal', 5],
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'cpa_ecpc',
        ],
        'hint' => '{{sold_partner_profit}} / {{cpa_accepted}}',
        'footer' => $formatter->asDecimal($model->getResultValue("cpa_ecpc_{$model->currency}"), 5)
      ],

      [
        'attribute' => 'cpr',
        'format' => ['decimal', '3'],
        'value' => function ($item) use ($model) {
          return $model->getCPR($item, $model->currency);
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'cpr',
        ],
        'hint' => Yii::_t('statistic.main_statistic.sold_partner_price') . ' / {{count_sold}}',
        'footer' => $formatter->asDecimal($model->getResultValue("cpr_{$model->currency}"), 3)
      ],

      [
        'attribute' => 'avg_cpa',
        'format' => ['decimal', '3'],
        'value' => function ($item) use ($model) {
          return $model->getAvgCPA($item, $model->currency);
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'avg_cpa',
        ],
        'hint' => '{{sold_partner_profit}} / {{count_sold}}',
        'footer' => $formatter->asDecimal($model->getResultValue("avg_cpa_{$model->currency}"), 3)
      ],
      [
        'key' => "rev_sub_{$model->currency}",
        'label' => Yii::_t('statistic.statistic.rev_sub'),
        'format' => ['decimal', '4'],
        'visible' => $model->canViewAdditionalStatistic(),
        'visibleInTemplate' => $model->canViewAdditionalStatistic(),
        'value' => function ($item) use ($model) {
          return $model->getRevSub($item, $model->currency);
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'class' => "rev_sub_{$model->currency}",
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'rev_sub',
        ],
        'hint' => Yii::_t('statistic.main_statistic.sold_sum_on_date') . ' / {{count_sold}}',
        'footer' => $formatter->asDecimal($model->getResultValue("rev_sub_{$model->currency}"), 4),
      ],
      [
        'key' => "roi_on_date_{$model->currency}",
        'label' => Yii::_t('statistic.statistic.roi_on_date'),
        'format' => ['decimal', 4],
        'visible' => $model->canViewAdditionalStatistic(),
        'visibleInTemplate' => $model->canViewAdditionalStatistic(),
        'value' => function ($item) use ($model) {
          return $model->getRoiOnDate($item, $model->currency);
        },
        'groupType' => self::CPA,
        'headerOptions' => [
          'class' => "roi_on_date_{$model->currency}",
          'data-group' => self::getHeaderGroups(self::CPA),
          'data-code' => 'roi_on_date',
        ],
        'hint' => '((' . Yii::_t('statistic.main_statistic.sold_sum_on_date') . ' / ' . Yii::_t('statistic.main_statistic.sold_partner_price') . ') - 1) * 100',
        'footer' => $formatter->asDecimal($model->getResultValue("roi_on_date_{$model->currency}"), 4),
      ],
      [
        'key' => 'onetime_accepted',
        'attribute' => 'onetime_accepted',
        'format' => 'integer',
        'value' => function ($item) use ($model) {
          return $model->getAcceptedValue($item, $model::ONETIME);
        },
        'groupType' => self::ONETIME,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::ONETIME),
          'data-code' => 'onetime_accepted',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('onetime_count_accepted'))
      ],
      [
        'key' => 'count_onetime',
        'attribute' => 'count_onetime',
        'format' => 'integer',
        'groupType' => self::ONETIME,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::ONETIME),
          'data-code' => 'count_onetime',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_onetime'))
      ],
      [
        'key' => 'partner_visible_count_onetime',
        'attribute' => 'partner_visible_count_onetime',
        'format' => 'integer',
        'groupType' => self::ONETIME,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::ONETIME),
          'data-code' => 'partner_visible_count_onetime',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('partner_visible_count_onetime'))
      ],
      [
        'footerOptions' => ['id' => 'onetime_ratio_total'],
        'attribute' => 'onetime_ratio',
        'value' => function ($item) use ($model) {
          return $model->getOnetimeRatio($item, '1:%s');
        },
        'groupType' => self::ONETIME,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::ONETIME),
          'data-code' => 'onetime_ratio',
        ],
        'footer' => $formatter->asRatio($model->getResultValue('onetime_ratio')),
        'hint' => '{{onetime_accepted}} / {{count_onetime}}',
        'contentOptions' => function ($item) use ($model) {
          return [
            'data-sort' => $model->getOnetimeRatio($item, '%s'),
          ];
        },
      ],
      [
        'footerOptions' => ['id' => 'onetime_cr_total'],
        'attribute' => 'onetime_cr',
        'format' => 'statisticSum',
        'value' => function ($item) use ($model) {
          return $model->getOnetimeCr($item);
        },
        'groupType' => self::ONETIME,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::ONETIME),
          'data-code' => 'onetime_cr',
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue('cr_onetime_ratio')),
        'hint' => '{{count_onetime}} / {{onetime_accepted}} * 100',
        'contentOptions' => function ($item) use ($model) {
          return [
            'data-sort' => $model->getOnetimeCr($item),
          ];
        },
      ],

      [
        'attribute' => 'partner_visible_onetime_cr',
        'format' => 'statisticSum',
        'value' => function ($item) use ($model) {
          return $model->getOnetimeCr($item, true);
        },
        'groupType' => self::ONETIME,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::ONETIME),
          'data-code' => 'partner_visible_onetime_cr',
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue('cr_partner_visible_onetime_ratio')),
        'hint' => '{{partner_visible_count_onetime}} / {{onetime_accepted}} * 100',
        'contentOptions' => function ($item) use ($model) {
          return [
            'data-sort' => $model->getOnetimeCr($item, true),
          ];
        },
      ],


      [
        'key' => 'onetime_reseller_profit',
        'attribute' => "onetime_reseller_profit_{$model->currency}",
        'label' => $model->getGridColumnLabel('onetime_reseller_profit'),
        'format' => 'statisticSum',
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::ONETIME,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => self::getHeaderGroups(self::ONETIME),
          'data-code' => "onetime_reseller_profit_{$model->currency}",
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue("onetime_reseller_profit_{$model->currency}"))
      ],
      [
        'attribute' => "onetime_reseller_net_profit_{$model->currency}",
        'label' => $model->getGridColumnLabel('reseller_net_profit'),
        'format' => 'statisticSum',
        'value' => function ($item) use ($model) {
          return $model->getOnetimeResellerNetProfit($item, $model->currency);
        },
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'visibleInTemplate' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::ONETIME,
        'headerOptions' => [
          'class' => self::CLASS_INT_COL,
          'data-group' => self::getHeaderGroups(self::ONETIME),
          'data-code' => "onetime_reseller_net_profit_{$model->currency}",
        ],
        'hint' => '{{onetime_reseller_profit}} - {{onetime_profit}}',
        'footer' => $formatter->asStatisticSum($model->getResultValue("onetime_reseller_net_profit_{$model->currency}"))
      ],
      [
        'key' => 'onetime_profit',
        'attribute' => "onetime_profit_{$model->currency}",
        'label' => $model->getGridColumnLabel('onetime_profit'),
        'format' => 'statisticSum',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency($model->currency),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::ONETIME,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::ONETIME),
          'data-code' => "onetime_profit_{$model->currency}",
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue("onetime_profit_{$model->currency}"))
      ],

      [
        'attribute' => 'onetime_ecpc',
        'value' => function ($item) use ($model) {
          return $model->getEcpcOnetime($item, $model->currency);
        },
        'format' => ['decimal', 5],
        'groupType' => self::ONETIME,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::ONETIME),
          'data-code' => 'onetime_ecpc',
        ],
        'hint' => '{{onetime_profit}} / {{onetime_accepted}}',
        'footer' => $formatter->asDecimal($model->getResultValue("onetime_ecpc_{$model->currency}"), 5)
      ],


      [
        'key' => 'sell_tb_accepted',
        'attribute' => 'sell_tb_accepted',
        'format' => 'integer',
        'visible' => $model->canViewSellTb(),
        'groupType' => self::SELL_TB,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::SELL_TB),
          'data-code' => 'sell_tb_accepted',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('sell_tb_accepted'))
      ],
      [
        'key' => 'count_sold_tb',
        'attribute' => 'count_sold_tb',
        'format' => 'integer',
        'groupType' => self::SELL_TB,
        'visible' => $model->canViewSellTb(),
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::SELL_TB),
          'data-code' => 'count_sold_tb',
        ],
        'footer' => $formatter->asInteger($model->getResultValue('count_sold_tb'))
      ],
      [
        'key' => 'sold_tb_reseller_profit',
        'attribute' => "sold_tb_reseller_profit_{$model->currency}",
        'label' => $model->getGridColumnLabel('sold_tb_reseller_profit'),
        'format' => 'statisticSum',
        'visible' => $model->canViewSellTb() && $model->canViewColumnByCurrency($model->currency),
        'visibleInTemplate' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::SELL_TB,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::SELL_TB),
          'data-code' => "sold_tb_reseller_profit_{$model->currency}",
        ],
        'footer' => $formatter->asStatisticSum($model->getResultValue("sold_tb_reseller_profit_{$model->currency}"))
      ],



      [
        'key' => 'reseller_total_profit',
        'label' => $model->getGridColumnLabel("reseller_total_profit"),
        'format' => 'statisticSum',
        'value' => function ($item) use ($model) {
          return $model->getResellerTotalProfit($item, $model->currency);
        },
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::TOTAL),
          'data-code' => "reseller_total_profit_{$model->currency}",
        ],
        'hint' => '{{sum_reseller_profit}} + {{cpa_profit}} + {{onetime_reseller_profit}} + {{sold_tb_reseller_profit}}',
        'footer' => $formatter->asStatisticSum($model->getResultValue("reseller_total_profit_{$model->currency}"))
      ],
      [
        'key' => 'reseller_net_profit',
        'label' => $model->getGridColumnLabel('reseller_net_profit'),
        'format' => 'statisticSum',
        'value' => function ($item) use ($model) {
          return $model->getNetProfitReseller($item, $model->currency);
        },
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => Yii::_t('statistic.statistic.total_profit'),
          'data-code' => "reseller_net_profit_{$model->currency}",
        ],
        'hint' => '{{reseller_total_profit}} - {{partner_total_profit}}',
        'footer' => $formatter->asStatisticSum($model->getResultValue("reseller_net_profit_{$model->currency}"))
      ],
      [
        'key' => 'partner_total_profit',
        'attribute' => "partner_total_profit_{$model->currency}",
        'label' => $model->getGridColumnLabel("partner_total_profit"),
        'format' => 'statisticSum',
        'value' => function ($item) use ($model) {
          return $model->getPartnerTotalProfit($item, $model->currency);
        },
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency($model->currency),
        'groupType' => self::TOTAL,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::TOTAL),
          'data-code' => "partner_total_profit_{$model->currency}",
        ],
        'hint' => '{{sum_profit}} + {{sold_partner_profit}} + {{onetime_profit}}',
        'footer' => $formatter->asStatisticSum($model->getResultValue("total_sum_{$model->currency}"))
      ],
      [
        'attribute' => 'count_complains',
        'label' => $model->getGridColumnLabel('count_complains'),
        'format' => 'raw',
        'visible' => $model->canViewComplainsStatistic(),
        'groupType' => self::COMPLAINS,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::COMPLAINS),
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
        'groupType' => self::COMPLAINS,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::COMPLAINS),
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
        'attribute' => 'count_calls_mno',
        'label' => $model->getGridColumnLabel('count_calls_mno'),
        'format' => 'raw',
        'visible' => $model->canViewComplainsStatistic(),
        'groupType' => self::COMPLAINS,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::COMPLAINS),
          'data-code' => 'count_calls_mno',
        ],
        'value' => function ($item) use ($model) {

          $urlParams = $model->getRowFilterArray($item);

          $urlParams['type'] = Complain::TYPE_CALL_MNO;

          $count = ArrayHelper::getValue($item, 'count_calls_mno');
          return $count
            ? Html::a($count ?: 0,
              array_merge(['detail/complains'], ['statistic' => $urlParams]),
              ['data-pjax' => 0])
            : '0';
        },
        'contentOptions' => function ($item) {
          $count = ArrayHelper::getValue($item, 'count_calls_mno');
          return [
            'data-sort' => $count ?: 0
          ];
        },
        'footer' => $formatter->asInteger($model->getResultValue('count_calls_mno'))
      ],
      [
        'footerOptions' => ['id' => 'complains_rate'],
        'attribute' => 'complains_rate',
        'format' => 'percent',
        'value' => function ($item) use ($model) {
          return $model->getComplainsRate($item);
        },
        'groupType' => self::COMPLAINS,
        'headerOptions' => [
          'data-group' => self::getHeaderGroups(self::COMPLAINS),
          'data-code' => 'complains_rate',
        ],
        'footer' => $formatter->asPercent($model->getResultValue('complains_rate')),
        'hint' => '({{count_complains}} + {{count_calls}} + {{count_calls_mno}}) / ({{count_ons}} + {{count_sold}} + {{count_onetime}})',
        'contentOptions' => function ($item) use ($model) {
          return [
            'data-sort' => $model->getComplainsRate($item),
          ];
        },
      ],
    ]);

    foreach ($gridColumns as &$column) {
      $column['class'] = StatisticColumn::class;
      // Если задана видимость = false, не показываем колонку
      $visible = ArrayHelper::getValue($column, 'visible', true);
      if (!$visible) continue;
    }

    if ($cache) self::$gridColumns[$templateId] = $gridColumns;

    return $gridColumns;
  }

  /**
   * Группировка колонок
   * @return string
   */
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

  /**
   * Названия групп столбцов
   * TRICKY кажется порядок групп ут должен быть такой же как колонок в гриде. Иначе у меня какой-то хаос получился.
   * @param string|null $group можно отфильтровать по коду. Тогда вернет только название типа в виде строки
   * @return array|string Массив [code => name] или просто name если отфильтровано по $group.
   */
  public static function getHeaderGroups($group = null)
  {
    $groups = [
      self::TRAFFIC => Yii::_t('statistic.statistic.traffic'),
      self::EFFICIENCY => Yii::_t('statistic.main_statistic.efficiency'),
      self::REVSHARE => Yii::_t('statistic.statistic.revshare'),
      self::CPA => Yii::_t('statistic.statistic.cpa'),
      self::ONETIME => Yii::_t('statistic.statistic.group_main_ik'),
      self::SELL_TB => Yii::_t('statistic.main_statistic.sell_tb'),
      self::TOTAL => Yii::_t('statistic.main_statistic.total'),
      self::COMPLAINS => Yii::_t('statistic.main_statistic.complains'),
    ];
    return $group ? ArrayHelper::getValue($groups, $group) : $groups;
  }

  /**
   * Видимые столбцы для шаблона. Нужно иметь список всех возможных столбцов для данного юзера
   * из-за фильтра по revshare/cpa. Нельзя сделать через StatisticColumn, т.к. невидимые столбцы удаляются
   * @param \mcms\statistic\models\mysql\Statistic|null $model
   * @param string|null $systemTemplateId @see ColumnTemplate::TEMPLATE_DEFAULT
   * TRICKY В $model нужно передавать или настояющую модель, или null. Передавать заглушку в виде new Statistic нельзя,
   * иначе появятся проблемы описанные в методе @see getGridColumns()
   * @return array
   */
  public static function getTemplateColumns($model = null, $systemTemplateId = null)
  {
    // Вместо настоящей модели Statistic установлена заглушка
    $isModelStub = false;

    if (!$model) {
      $isModelStub = true;
      $model = new MainAdminStatistic;
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

        if ($systemTemplateId && !in_array($systemTemplateId, (array)ArrayHelper::getValue($column, 'templates', []))) {
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

  /**
   * Creates column objects and initializes them.
   * @throws \yii\base\InvalidConfigException
   */
  protected function initColumns()
  {
    if (empty($this->columns)) {
      $this->guessColumns();
    }
    foreach ($this->columns as $i => $column) {
      if (is_string($column)) {
        $column = $this->createDataColumn($column);
      } else {
        $column = Yii::createObject(array_merge([
          'class' => $this->dataColumnClass ? : DataColumn::class,
          'grid' => $this,
        ], $column));
      }

      $this->allColumns[] = $column; // <-- вот тут кастомизация

      if (!$column->visible) {
        unset($this->columns[$i]);
        continue;
      }
      $this->columns[$i] = $column;
    }
  }
}
