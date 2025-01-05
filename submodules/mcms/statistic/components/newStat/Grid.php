<?php

namespace mcms\statistic\components\newStat;

use Closure;
use kartik\grid\GridView;
use mcms\statistic\components\Formattable;
use mcms\statistic\components\newStat\mysql\Row;
use mcms\statistic\models\ColumnsTemplateNew as ColumnsTemplate;
use Yii;
use yii\base\InvalidParamException;
use yii\db\Exception;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

/**
 * Грид статы "на стероидах" :)
 */
class Grid extends GridView
{
  const HEAD_GROUP_TRAFFIC_REV = 'traffic_rev';
  const HEAD_GROUP_PERFOMANCE_REV = 'perfomance_rev';
  const HEAD_GROUP_COHORTS_REV = 'cohorts_rev';
  const HEAD_GROUP_CUSTOMER_BASE_REV = 'customer_base_rev';
  const HEAD_GROUP_REVENUES_REV = 'revenues_rev';
  const HEAD_GROUP_COMPLAINTS_REV = 'complaints_rev';

  const HEAD_GROUP_TRAFFIC_CPA = 'traffic_cpa';
  const HEAD_GROUP_PERFOMANCE_CPA = 'perfomance_cpa';
  const HEAD_GROUP_COHORDS_CPA = 'cohords_cpa';
  const HEAD_GROUP_CUSTOMER_BASE_CPA = 'customer_base_cpa';
  const HEAD_GROUP_REVENUES_CPA = 'revenues_cpa';
  const HEAD_GROUP_CUSTOMER_CARE_CPA = 'customer_care_cpa';

  const HEAD_GROUP_TRAFFIC_OTP = 'traffic_otp';
  const HEAD_GROUP_PERFOMANCE_OTP = 'perfomance_otp';
  const HEAD_GROUP_COHORDS_OTP = 'cohords_otp';
  const HEAD_GROUP_CUSTOMER_BASE_OTP = 'customer_base_otp';
  const HEAD_GROUP_REVENUES_OTP = 'revenues_otp';
  const HEAD_GROUP_CUSTOMER_CARE_OTP = 'customer_care_otp';

  const HEAD_GROUP_TRAFFIC_TOTAL = 'traffic_total';
  const HEAD_GROUP_AFFILIATE_TOTAL = 'affiliate_total';
  const HEAD_GROUP_COHORTS_TOTAL = 'cohorts_total';
  const HEAD_GROUP_CUSTOMER_BASE_TOTAL = 'customer_base_total';
  const HEAD_GROUP_REVENUES_TOTAL = 'revenues_total';
  const HEAD_GROUP_CUSTOMER_CARE_TOTAL = 'customer_care_total';

  const HEAD_GROUP_GROUP = 'group';

  const HEAD_GROUP_CATEGORY_TOTAL = 'total';
  const HEAD_GROUP_CATEGORY_REVSHARE = 'revshare';
  const HEAD_GROUP_CATEGORY_CPA = 'cpa';
  const HEAD_GROUP_CATEGORY_OTP = 'otp';


  /** @inheritdoc */
  public $layout = "{items}
      <div class=\"dt-toolbar-footer\">
      <div class=\"col-sm-6 col-xs-12 hidden-xs\">
        <div class=\"dataTables_info\" id=\"dt_basic_info\" role=\"status\" aria-live=\"polite\">{summary}
        </div>
      </div>
      <div class=\"col-xs-12 col-sm-6 dataTables_paginate paging_simple_numbers\">{pager}</div>
    </div>";
  /** @inheritdoc */
  public $emptyCell = '';
  /** @inheritdoc */
  public $dataColumnClass = Column::class;
  /** @var  DataProvider */
  public $dataProvider;
  /** @var FormModel */
  public $statisticModel;
  /** @var array колонки для экспорта */
  public $_exportColumns;
  /**  @var int выбранный шаблон */
  public $templateId;
  /** @var bool только экспорт. Если установить в true, ассеты не будут зарегистрированы */
  public $isExportOnly;
  /** @var bool Показывать строки Итого и Среднее */
  public $showSummary = true;
  /** @inheritdoc */
  public $rowOptions = ['class' => 'tbody'];
  public $resizableColumns = false;

  /**
   * @var string[] массив-кэш для @see static::getKeyIndexedLabels()
   */
  private static $keyIndexedLabels;

  private static $_templateColumnsList;

  /** @var array Массив колонок, сгруппированный по ключу группы колонок*/
  protected $groupedColumns;

  /**
   * @throws Exception
   */
  public function init()
  {
    if (!$this->statisticModel) {
      throw new Exception('Statistc model not exists');
    }

    if (!$this->dataProvider) {
      throw new Exception('DataProvider not exists');
    }

    if (!$this->templateId) {
      throw new Exception('Template ID not exists');
    }

    $this->groupedColumns = $this->getTemplateColumns($this->templateId);

    parent::init();

    $this->beforeHeader = $this->getBeforeHeader();

    $this->tableOptions = [
        'id' => 'statisticTable',
        'data-template-columns' => $this->getAllTemplateColumns(),
      ] + $this->tableOptions;

    if (!$this->isExportOnly){
      $this->registerJs();
    }

    if ($this->statisticModel instanceof Formattable) {
      Yii::configure($this->formatter, $this->statisticModel->getFormatterParams());
    }
  }

  /**
   * Вставляем итого и среднее в таблицу
   * @inheritdoc
   */
  public function renderTableBody()
  {
    return preg_replace("/<tbody>\n/", '$0' . $this->renderTableSummary(), parent::renderTableBody());
  }

  /**
   * Строки итого и среднее
   * @return string the rendering result.
   */
  public function renderTableSummary()
  {
    if (!$this->showSummary) {
      return '';
    }
    $cellsSum = [];
    foreach ($this->columns as $column) { /* @var $column Column */
      $column->sum = Column::getMainAttributeSpan($this->formatter->format($column->sum, $column->format));
      if ($column->addAttribute && !$column->isHideTotalAdd()) {
        $column->sum .= Column::getAddAttributeSpan($this->formatter->format($column->addSum, $column->addFormat));
      }
      /* @var $column Column */
      $cellsSum[] = Html::tag('td', trim($column->sum) !== '' ? $column->sum : $this->emptyCell, [
        'class' => $column->getColumnIsSorted() ? 'sorted' : null
      ]);
    }
    $content = Html::tag('tr', implode('', $cellsSum), ['class' => 'total']);

    $cellsAvg = [];
    foreach ($this->columns as $column) {
      $column->avg = Column::getMainAttributeSpan($this->formatter->format($column->avg, $column->getAvgFormat()));
      if ($column->addAttribute && !$column->isHideTotalAdd()) {
        $column->avg .= Column::getAddAttributeSpan($this->formatter->format($column->addAvg, $column->getAvgAddFormat()));
      }
      /* @var $column Column */
      $cellsAvg[] = Html::tag('td', trim($column->avg) !== '' ? $column->avg : $this->emptyCell, [
        'class' => $column->getColumnIsSorted() ? 'sorted' : null
      ]);
    }
    $content .= Html::tag('tr', implode('', $cellsAvg), ['class' => 'avg']);

    return $content;
  }

  /**
   * TRICKY: Оставляем таблицу без классов
   */
  protected function initBootstrapStyle()
  {
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
    $groupsCount = count($this->statisticModel->groups);
    $this->view->registerJs(/** @lang JavaScript */
      "window.fixedColumnsCount = $groupsCount;",
      View::POS_HEAD);

    $this->getView()->registerJs(/** @lang JavaScript */'
      var $dateRange = $("#formmodel-daterange");
      $(document).on("click", ".change_date", function (e) {
        e.preventDefault();
        $dateRange.daterangepicker({
          startDate: $(this).data("start"),
          endDate: $(this).data("end"),
          locale: {
            format: "YYYY-MM-DD"
          }
        });
        if (!window.SETTING_AUTO_SUBMIT) {
          $("#statistic-filter-form").find("button[type=submit]").trigger("click");
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
   * получить сырой вариант массива колонок
   * @return array
   */
  private static function getGridColums()
  {
    return require __DIR__ . '/columns.php';
  }

  /**
   * Вернуть колонки, которые указаны в шаблоне (сгруппированные)
   * @param int $templateId
   * @return array
   */
  public function getTemplateColumns($templateId)
  {
    // Колонки, доступные для шаблона
    $templateColumns = static::getTemplateColumnsList($templateId);

    // выбираем только колонки, которые есть в шаблоне
    return array_map(function ($columns) use ($templateColumns) {
      return array_filter($columns, function ($column) use ($templateColumns) {
        $attribute = ArrayHelper::getValue($column, 'attribute');
        return in_array($attribute, $templateColumns) && $this->isVisible($column);
      });
    }, static::getGridColums());
  }

  /**
   * Список колонок шаблона
   * @param int|null $templateId
   * @return array|mixed|string
   */
  public static function getTemplateColumnsList($templateId = null)
  {
    $templateFields = ArrayHelper::getValue(self::$_templateColumnsList, $templateId);
    if ($templateFields !== null) {
      return $templateFields;
    }
    if ($templateId === null) {
      return [];
    }
    // Если шаблон системный, берем его колонки
    $systemTemplates = ColumnsTemplate::getSystemTemplates();
    /** @var ColumnsTemplate $templateModel */
    $templateModel = ArrayHelper::getValue($systemTemplates, $templateId);
    if ($templateModel) {
      self::$_templateColumnsList[$templateId] =  Json::decode($templateModel->columns);
      return self::$_templateColumnsList[$templateId];
    }

    // Объект выбранного шаблона
    $templateModel = ColumnsTemplate::getTemplate($templateId);
    // Колонки, доступные для шаблона
    self::$_templateColumnsList[$templateId] = $templateModel ? Json::decode($templateModel->columns) : [];
    return self::$_templateColumnsList[$templateId];
  }

  /**
   * Вернуть колонки системного шаблона
   * @param int|null $templateId
   * @return array
   */
  public static function getSystemTemplateColumns($templateId)
  {
    $attributes = [];
    foreach (static::getGridColums() as $columns) {
      $templateAttributes = array_filter($columns, function ($column) use ($templateId) {
        // Есть в шаблоне (или шаблон со всеми столбцами)
        $inTemplate = $templateId == ColumnsTemplate::SYS_TEMPLATE_ALL ||
          in_array($templateId, (array)ArrayHelper::getValue($column, 'template', []));
        // Видимый
        $visible = ArrayHelper::getValue($column, 'visible', true);

        return $inTemplate && $visible;
      });
      $attributes = array_merge($attributes, $templateAttributes);
    }
    return $attributes;
  }

  /**
   * Колонки для экспорта
   * @return array
   */
  public function getExportColumns()
  {
    if (!empty($this->_exportColumns)) {
      return $this->_exportColumns;
    }

    foreach ($this->columns as $column) {
      /** @var Column $column */
      //убираем валюту в форматтере currencyCustomDecimal
      if (is_array($column->format) && $column->format[0] === 'currencyCustomDecimal') {
        $column->format[1] = '';
      }
      if (is_array($column->addFormat) && $column->addFormat[0] === 'currencyCustomDecimal') {
        $column->addFormat[1] = '';
      }
      $this->formatter->thousandSeparator = '';
      $this->_exportColumns[] = [
        'label' => $column->label,
        'attribute' => $column->attribute,
        'value' => $column->value,
        'format' => $column->format,
        'excelFormat' => $column->excelFormat,
        'footer' => [
          // названия групп в экспорте не форматтируем, иначе будет (not set)
          $column->attribute === 'groups' && $column->sum === null ? null : $this->formatter->format($column->sum, $column->format),
          $column->attribute === 'groups' && $column->sum === null ? null : $this->formatter->format($column->avg, $column->getAvgFormat()),
        ]
      ];
      if ($column->addAttribute || $column->addValue) {
        $this->_exportColumns[] = [
          'label' => $column->label,
          'attribute' => $column->addAttribute,
          'value' => $column->addValue,
          'format' => $column->addFormat,
          'footer' => [
            $this->formatter->format($column->addSum, $column->addFormat),
            $this->formatter->format($column->addAvg, $column->getAvgAddFormat()),
          ]
        ];
      }
    }
    return $this->_exportColumns;
  }

  /**
   * Группировка колонок
   * @return string
   */
  public function getBeforeHeader()
  {
    $headerHtmls = [];

    foreach ($this->statisticModel->groups as $groupKey) {
      // Пустая ячейка для группировки
      $headerHtmls[] = Html::tag('th', '');
    }

    foreach ($this->groupedColumns as $groupKey => $groupColumns) {
      $count = count(array_filter($groupColumns, function ($column) {
        return $this->isVisible($column);
      }));

      if ($count) {
        $headerHtmls[] = Html::tag('th', static::getHeaderGroup($groupKey), ['colspan' => $count]);
      }
    }

    return implode('', $headerHtmls);
  }

  /**
   * Названия групп столбцов
   * @return array Массив [code => name]
   */
  public static function getHeaderGroups()
  {
    $result = [];
    foreach (static::getHeaderGroupsByCategory() as $category) {
      $result = array_merge($result, $category);
    }
    return $result;
  }

  /**
   * Названия групп столбцов с разбивкой по категориям
   * @return array Массив [category_code => [code => name]]
   */
  protected static function getHeaderGroupsByCategory()
  {
    return [
      self::HEAD_GROUP_CATEGORY_REVSHARE => [
        self::HEAD_GROUP_TRAFFIC_REV => Yii::_t('statistic.new_statistic_refactored.head_group-traffic'),
        self::HEAD_GROUP_PERFOMANCE_REV => Yii::_t('statistic.new_statistic_refactored.head_group-perfomance'),
        self::HEAD_GROUP_COHORTS_REV => Yii::_t('statistic.new_statistic_refactored.head_group-cohorts'),
        self::HEAD_GROUP_CUSTOMER_BASE_REV => Yii::_t('statistic.new_statistic_refactored.head_group-customer_base'),
        self::HEAD_GROUP_REVENUES_REV => Yii::_t('statistic.new_statistic_refactored.head_group-revenues'),
        self::HEAD_GROUP_COMPLAINTS_REV => Yii::_t('statistic.new_statistic_refactored.head_group-customer_care'),
      ],
      self::HEAD_GROUP_CATEGORY_CPA => [
        self::HEAD_GROUP_TRAFFIC_CPA => Yii::_t('statistic.new_statistic_refactored.head_group-traffic'),
        self::HEAD_GROUP_PERFOMANCE_CPA => Yii::_t('statistic.new_statistic_refactored.head_group-perfomance'),
        self::HEAD_GROUP_COHORDS_CPA => Yii::_t('statistic.new_statistic_refactored.head_group-cohorts'),
        self::HEAD_GROUP_CUSTOMER_BASE_CPA => Yii::_t('statistic.new_statistic_refactored.head_group-customer_base'),
        self::HEAD_GROUP_REVENUES_CPA => Yii::_t('statistic.new_statistic_refactored.head_group-revenues'),
        self::HEAD_GROUP_CUSTOMER_CARE_CPA => Yii::_t('statistic.new_statistic_refactored.head_group-customer_care'),
      ],
      self::HEAD_GROUP_CATEGORY_OTP => [
        self::HEAD_GROUP_TRAFFIC_OTP => Yii::_t('statistic.new_statistic_refactored.head_group-traffic'),
        self::HEAD_GROUP_PERFOMANCE_OTP => Yii::_t('statistic.new_statistic_refactored.head_group-perfomance'),
        self::HEAD_GROUP_COHORDS_OTP => Yii::_t('statistic.new_statistic_refactored.head_group-cohorts'),
        self::HEAD_GROUP_CUSTOMER_BASE_OTP => Yii::_t('statistic.new_statistic_refactored.head_group-customer_base'),
        self::HEAD_GROUP_REVENUES_OTP => Yii::_t('statistic.new_statistic_refactored.head_group-revenues'),
        self::HEAD_GROUP_CUSTOMER_CARE_OTP => Yii::_t('statistic.new_statistic_refactored.head_group-customer_care'),
      ],
      self::HEAD_GROUP_CATEGORY_TOTAL => [
        self::HEAD_GROUP_TRAFFIC_TOTAL => Yii::_t('statistic.new_statistic_refactored.head_group-traffic'),
        self::HEAD_GROUP_AFFILIATE_TOTAL => Yii::_t('statistic.new_statistic_refactored.head_group-affiliate_payout'),
        self::HEAD_GROUP_COHORTS_TOTAL => Yii::_t('statistic.new_statistic_refactored.head_group-cohorts'),
        self::HEAD_GROUP_CUSTOMER_BASE_TOTAL => Yii::_t('statistic.new_statistic_refactored.head_group-customer_base'),
        self::HEAD_GROUP_REVENUES_TOTAL => Yii::_t('statistic.new_statistic_refactored.head_group-revenues'),
        self::HEAD_GROUP_CUSTOMER_CARE_TOTAL => Yii::_t('statistic.new_statistic_refactored.head_group-customer_care'),
      ]
    ];
  }

  /**
   * Все категории столбцов (Total, Revshare, CPA, OTP)
   * @return array
   */
  public static function getHeaderCategories()
  {
    return [
      self::HEAD_GROUP_CATEGORY_TOTAL => Yii::_t('statistic.new_statistic_refactored.head_category-total'),
      self::HEAD_GROUP_CATEGORY_REVSHARE => Yii::_t('statistic.new_statistic_refactored.head_category-revshare'),
      self::HEAD_GROUP_CATEGORY_CPA => Yii::_t('statistic.new_statistic_refactored.head_category-cpa'),
      self::HEAD_GROUP_CATEGORY_OTP => Yii::_t('statistic.new_statistic_refactored.head_category-otp'),
    ];
  }

  /**
   * Название группы столбцов по её коду
   * @param string $group код группы
   * @return string вернет название типа в виде строки
   */
  public static function getHeaderGroup($group)
  {
    return ArrayHelper::getValue(static::getHeaderGroups(), $group);
  }

  /**
   * Код категории столбцов (Total, Revshare, CPA, OTP) по коду группы
   * @param string $group код группы
   * @return string вернет код в виде строки
   */
  public static function getHeaderCategoryKey($group)
  {
    $result = '';
    foreach (static::getHeaderGroupsByCategory() as $key => $category) {
      if (ArrayHelper::keyExists($group, $category)) {
        $result = $key;
        break;
      }
    }
    return $result;
  }

  /**
   * Название категории столбцов (Total, Revshare, CPA, OTP) по коду
   * @param string $key код категории
   * @return string вернет название в виде строки
   */
  public static function getHeaderCategoryLabel($key)
  {
    return ArrayHelper::getValue(static::getHeaderCategories(), $key);
  }


  /**
   * Creates column objects and initializes them.
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  protected function initColumns()
  {
    $this->columns = [];

    $firstGroup = reset($this->statisticModel->groups);
    foreach ($this->statisticModel->groups as $groupKey) {
      $config = [
        'attribute' => 'groups',
        'isGroupColumn' => true,
        'label' => Group::getGroupColumnLabel($groupKey),
        'format' => 'raw',
        'sum' => $groupKey === $firstGroup ? Yii::_t('statistic.new_statistic_refactored.footer_total') : null,
        'avg' => $groupKey === $firstGroup ? Yii::_t('statistic.new_statistic_refactored.footer_avg') : null,
        'excelFormat' => $groupKey === Group::BY_DATES ? 'date' : null,
        'value' => function (Row $row) use ($groupKey) {
          /** @var Group  $group */
          $group = ArrayHelper::getValue($row->getGroups(), $groupKey);
          if (!$group || $group->getValue() === null) {
            return null;
          }
          return $group->getFormattedValue();
        },
      ];
      $this->columns[] = $this->initColumn($config, self::HEAD_GROUP_GROUP);
    }
    if ($this->statisticModel->secondGroup) {
      $groupKey = $this->statisticModel->secondGroup;
      $config = [
        'attribute' => 'groups',
        'isGroupColumn' => true,
        'label' => Group::getGroupColumnLabel($groupKey),
        'format' => 'raw',
        'sum' => '',
        'avg' => '',
        'excelFormat' => $groupKey === Group::BY_DATES ? 'date' : null,
        'value' => function (Row $row) use ($groupKey) {
          /** @var Group  $group */
            $group = ArrayHelper::getValue($row->getSecondGroup(), $groupKey);
          if (!$group || $group->getValue() === null) {
            return null;
          }
          return $group->getFormattedValue();
        },
      ];
      $this->columns[] = $this->initColumn($config, self::HEAD_GROUP_GROUP);
    }

    foreach ($this->groupedColumns as $groupKey => $groupColumns) {
      /** @var array $groupColumns */
      foreach ($groupColumns as $column) {
        if (!isset($column['attribute'])) {
          throw new InvalidParamException('$attribute field is mandatory for stat grid column');
        }

        $column = $this->initColumn($column, $groupKey);

        $this->columns[] = $column;
      }
    }

    $this->initTooltips();

    $this->unsetInvisibleColumns();
  }

  /**
   * Инит колонки
   * @param $config
   * @param $group
   * @return Column
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  protected function initColumn($config, $group)
  {
    /** @var Column $column */
    $column = is_string($config)
      ? $this->createDataColumn($config)
      : Yii::createObject(array_merge([
        'class' => $this->dataColumnClass ? : DataColumn::class,
        'grid' => $this,
      ], $config));

    $column->group = $group;

    if (!$column->sum && $column->group !== self::HEAD_GROUP_GROUP) {
      if ($column->value instanceof Closure) {
        $closure = $column->value;
        $column->sum = $closure($this->dataProvider->sumRow);
        $column->avg = $closure($this->dataProvider->avgRow);
      } else {
        $column->sum = $this->dataProvider->sumRow->{$column->attribute};
        $column->avg = $this->dataProvider->avgRow->{$column->attribute};
      }

      if ($column->addValue instanceof Closure) {
        $closure = $column->addValue;
        $column->addSum = $closure($this->dataProvider->sumRow);
        $column->addAvg = $closure($this->dataProvider->avgRow);
      } elseif ($column->addAttribute !== null) {
        $column->addSum = $this->dataProvider->sumRow->{$column->addAttribute};
        $column->addAvg = $this->dataProvider->avgRow->{$column->addAttribute};
      }
    }
    $column->headerOptions['data-code'] = $column->attribute;

    return $column;
  }

  private function unsetInvisibleColumns()
  {
    foreach ($this->columns as $key => $column) {
      if (!$column->visible) {
        unset($this->columns[$key]);
      }
    }
  }

  private function initTooltips()
  {
    foreach ($this->columns as &$column) {
      /** @var Column $column */
      if (isset($column->headerOptions['title'])) {
        continue;
      }

      $column->headerLabelOptions = [
        'class' => 'header-popover',
        'rel' => 'popover-hover',
        'data-placement' => 'bottom',
        'data-html' => 'true',
        'data-toggle' => 'popover',
        'data-trigger' => 'hover',
        'data-content' => strtr($column->hint, $this->getKeyIndexedLabels()),
      ];
    }
  }

  /**
   * Массив в виде ['{{count_hits}}' => '(Трафик: Клики)', '{{count_tb}}' => '(Трафик: ТБ)']
   * В виде значение отформатированное название столбца по шаблону @see StatisticColumn::$hintColumnFormat
   * @return array
   */
  protected function getKeyIndexedLabels()
  {
    if (self::$keyIndexedLabels !== null) {
      return self::$keyIndexedLabels;
    }

    self::$keyIndexedLabels = [];
    $row = new Row();
    foreach (static::getGridColums() as $groupCode => $colums) {
      foreach ($colums as $column) {
        $attribute = ArrayHelper::getValue($column, 'attribute');

        self::$keyIndexedLabels['{{' . $attribute . '}}'] = strtr(
          Column::$hintColumnFormat,
          [
            '[group]' => static::getHeaderGroup($groupCode),
            '[label]' => $row->getAttributeLabel($attribute),
          ]
        );
      }
    }

    return self::$keyIndexedLabels;
  }

  /**
   * Видимые столбцы для шаблона. Нужно иметь список всех возможных столбцов для данного юзера
   * @return array
   */
  public function getAllTemplateColumns()
  {
    $templateColumns = [];
    $row = new Row();
    foreach (static::getGridColums() as $groupCode => $colums) {
      foreach ($colums as $column) {
        if (!$this->isVisible($column)) {
          continue;
        }
        $attribute = ArrayHelper::getValue($column, 'attribute');
        $categoryKey = static::getHeaderCategoryKey($groupCode);
        $templateColumns[] = [
          'code' => $attribute,
          'text' => $row->getAttributeLabel($attribute),
          'group' => $groupCode,
          'groupLabel' => static::getHeaderGroup($groupCode),
          'category' => $categoryKey,
          'categoryLabel' => static::getHeaderCategoryLabel($categoryKey),
        ];
      }
    }

    return $templateColumns;
  }

  /**
   * Проверка на видимость колонки
   * @param $column
   * @return bool
   */
  private function isVisible($column)
  {
    if (isset($column['visible'])) {
      if ($column['visible'] instanceof Closure) {
        return call_user_func($column['visible'], $this->statisticModel);
      }

      return $this->statisticModel->getPermissionsChecker()->{$column['visible']}();
    }

    return true;
  }
}
