<?php

namespace mcms\statistic\components\mainStat;

use mcms\common\widget\AdminGridView;
use mcms\statistic\components\mainStat\mysql\Row;
use mcms\statistic\models\ColumnsTemplate;
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
class Grid extends AdminGridView
{
  const HEAD_GROUP_TRAFFIC = 'traffic';
  const HEAD_GROUP_CPA = 'cpa';
  const HEAD_GROUP_REVSHARE = 'revshare';
  const HEAD_GROUP_EFFICIENCY = 'efficiency';
  const HEAD_GROUP_ONETIME = 'onetime';
  const HEAD_GROUP_SELL_TB = 'sell_tb';
  const HEAD_GROUP_TOTAL = 'total';
  const HEAD_GROUP_COMPLAINS = 'complains';
  const HEAD_GROUP_GROUP = 'group';
  /**
   * Класс, который нужно подставить в столбцы с числами, чтобы сортировка велась корректно
   */
  const CLASS_INT_COL = 'datatable-int-col';


  /** @inheritdoc */
  public $layout = '{items}';
  /** @inheritdoc */
  public $condensed = true;
  /** @inheritdoc */
  public $responsiveWrap = false;
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

  /**
   * @var string[] массив-кэш для @see self::getKeyIndexedLabels()
   */
  private static $keyIndexedLabels;

  /** @var array Массив колонок, сгруппированный по ключу группы колонок*/
  protected $groupedColumns;

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
        'class' => 'table nowrap text-center data-table dataTable',
        'id' => 'statistic-data-table',
        'data-skip-summary-calculation' => '0',
        'data-empty-result' => Yii::t('yii', 'No results found.'),
        'data-class-int-col' => self::CLASS_INT_COL,
        'data-template-columns' => $this->getAllTemplateColumns(),
      ] + $this->tableOptions;

    $this->showFooter = $this->dataProvider->getTotalCount() > 0;

    if (!$this->isExportOnly){
      $this->registerJs();
    }
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
      var $startDate = $("#formmodel-datefrom"),
          $endDate = $("#formmodel-dateto");
      
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
    $templateColumns = self::getTemplateColumnsList($templateId);

    // выбираем только колонки, которые есть в шаблоне
    return array_map(function ($columns) use ($templateColumns) {
      return array_filter($columns, function ($column) use ($templateColumns) {
        $attribute = ArrayHelper::getValue($column, 'attribute');
        return in_array($attribute, $templateColumns) && $this->isVisible($column);
      });
    }, self::getGridColums());
  }

  /**
   * Список колонок шаблона
   * @param int|null $templateId
   * @return array|mixed|string
   */
  private static function getTemplateColumnsList($templateId = null)
  {
    if ($templateId === null) {
      return [];
    }
    // Если шаблон системный, берем его колонки
    $systemTemplates = ColumnsTemplate::getSystemTemplates();
    /** @var ColumnsTemplate $templateModel */
    $templateModel = ArrayHelper::getValue($systemTemplates, $templateId);
    if ($templateModel) {
      return Json::decode($templateModel->columns);
    }

    // Объект выбранного шаблона
    $templateModel = ColumnsTemplate::getTemplate($templateId);
    // Колонки, доступные для шаблона
    return $templateModel ? Json::decode($templateModel->columns) : [];
  }

  /**
   * Вернуть колонки системного шаблона
   * @param int|null $templateId
   * @return array
   */
  public static function getSystemTemplateColumns($templateId)
  {
    $attributes = [];
    foreach (self::getGridColums() as $columns) {
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
    if (!empty($this->_exportColumns)) return $this->_exportColumns;

    foreach ($this->columns as $column) {
      /** @var Column $column */
      $this->_exportColumns[] = [
        'label' => $column->label,
        'attribute' => $column->attribute,
        'value' => $column->value,
        'format' => $column->format,
        'footer' => $column->footer,
      ];
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
      $headerHtmls[] = Html::tag('th', Group::getGroupColumnLabel($groupKey), ['rowspan' => 2]);
    }

    foreach ($this->groupedColumns as $groupKey => $groupColumns) {
      $count = count(array_filter($groupColumns, function ($column) {
        return $this->isVisible($column);
      }));

      if ($count) {
        $headerHtmls[] = Html::tag('td', self::getHeaderGroup($groupKey), ['colspan' => $count]);
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
    return [
      self::HEAD_GROUP_TRAFFIC => Yii::_t('statistic.statistic.traffic'),
      self::HEAD_GROUP_EFFICIENCY => Yii::_t('statistic.main_statistic.efficiency'),
      self::HEAD_GROUP_REVSHARE => Yii::_t('statistic.statistic.revshare'),
      self::HEAD_GROUP_CPA => Yii::_t('statistic.statistic.cpa'),
      self::HEAD_GROUP_ONETIME => Yii::_t('statistic.statistic.group_main_ik'),
      self::HEAD_GROUP_SELL_TB => Yii::_t('statistic.main_statistic.sell_tb'),
      self::HEAD_GROUP_TOTAL => Yii::_t('statistic.main_statistic.total'),
      self::HEAD_GROUP_COMPLAINS => Yii::_t('statistic.main_statistic.complains'),
    ];
  }

  /**
   * Название группы столбцов по её коду
   * @param string $group код группы
   * @return string вернет название типа в виде строки
   */
  public static function getHeaderGroup($group)
  {
    return ArrayHelper::getValue(self::getHeaderGroups(), $group);
  }


  /**
   * Creates column objects and initializes them.
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  protected function initColumns()
  {
    $this->columns = [];

    $lastGroup = end($this->statisticModel->groups);
    foreach ($this->statisticModel->groups as $groupKey) {
      $config = [
        'label' => Group::getGroupColumnLabel($groupKey),
        'format' => 'raw',
        'footer' => $groupKey === $lastGroup ? Yii::_t('statistic.main_statistic_refactored.footer_total') : null,
        'value' => function (Row $row) use ($groupKey) {
          /** @var Group  $group */
          $group = ArrayHelper::getValue($row->getGroups(), $groupKey);
          if (!$group || $group->getValue() === null) {
            return null;
          }
          return $group->getFormattedValue();
        },
        'contentOptions' => function (Row $row) use ($groupKey) {
          /** @var Group  $group */
          $group = ArrayHelper::getValue($row->getGroups(), $groupKey);
          return [
            'data-sort' => $groupKey === Group::BY_HOURS ? mktime($group->getValue()) : $group->getValue()
          ];
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

    // для int колонок добавляем класс чтоб datatables смог нормально сортировать их
    if ($column->format === 'integer') {
      $column->headerOptions['class'] = self::CLASS_INT_COL;
    }

    if (!$column->footer && $column->group !== self::HEAD_GROUP_GROUP) {
      $column->footer = $this->formatter->format($this->dataProvider->footerRow->{$column->attribute}, $column->format);
    }

    $column->headerOptions['data-code'] = $column->attribute;
    if ($column->group !== self::HEAD_GROUP_GROUP) {
      $column->contentOptions = function (Row $row) use ($column) {
        return [
          'data-sort' => $row->{$column->attribute}
        ];
      };
    }


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
      $column->headerOptions['title'] = strtr($column->hint, $this->getKeyIndexedLabels());
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
    foreach (self::getGridColums() as $groupCode => $colums) {
      foreach ($colums as $column) {
        $attribute = ArrayHelper::getValue($column, 'attribute');

        self::$keyIndexedLabels['{{' . $attribute . '}}'] = strtr(
          Column::$hintColumnFormat,
          [
            '[group]' => self::getHeaderGroup($groupCode),
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
    foreach (self::getGridColums() as $groupCode => $colums) {
      foreach ($colums as $column) {
        if (!$this->isVisible($column)) {
          continue;
        }
        $attribute = ArrayHelper::getValue($column, 'attribute');
        $templateColumns[] = [
          'code' => $attribute,
          'text' => $row->getAttributeLabel($attribute),
          'group' => $groupCode,
          'groupLabel' => self::getHeaderGroup($groupCode),
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
      return $this->statisticModel->getPermissionsChecker()->{$column['visible']}();
    }

    return true;
  }
}
