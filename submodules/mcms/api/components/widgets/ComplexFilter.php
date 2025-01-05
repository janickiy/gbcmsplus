<?php

namespace mcms\mcms\api\components\widgets;


use mcms\api\components\BaseMapper;
use mcms\api\components\MapperBuilder;
use mcms\api\components\MapperData;
use mcms\api\components\MapperDataParser;
use mcms\api\components\HttpQueryParser;
use mcms\api\components\widgets\assets\ComplexFilterAsset;
use ReflectionClass;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Виджет сложных фильтров с возможностью js или ajax поиска
 * Пример использования:
 *
 * ComplexFilter::widget([
 *   'id' => 'Ajax',
 *   'label' => 'Партнеры',
 *   'formName' => 'FormModel',
 *   'fieldName' => 'users',
 *   'relatedFieldName' => 'streams',
 *   'searchFields' => ['id', 'name', 'email', 'streams__id', 'streams__name'],
 *   'fieldLabelMask' => '#{id} - {email}',
 *   'relatedFieldLabelMask' => '#{id} - {name}',
 *   'isAjax' => true,
 *   'mapperName' => 'partners',
 *   'fields' => ['id', 'email', 'streams__id', 'streams__name'],
 *   'orderFields' => ['id'],
 *   ]);
 */
class ComplexFilter extends Widget
{
  private static $count;

  public $label;
  public $url;
  public $isAjax = false;
  /** @var int лимит записей */
  public $limit = 10;
  /** @var int лимит вложеных записей */
  public $relatedLimit = 10;
  /** @var string имя поля */
  public $fieldName;
  /** @var string имя вложенного поля */
  public $relatedFieldName;
  /** @var string форма */
  public $formName;
  /** @var array Поля, по которым осуществляется поиск */
  public $searchFields = [];
  /**
   * @var string шаблон для отображения лейбла поля. Пример '#{id} - {name}'
   * Можно делать лейблы типа {if url}la-la-la{/if}. Строка 'la-la-la' будет показана, если url не пустой
   */
  public $fieldLabelMask = 'field label';
  /** @var string шаблон для отображения лейбла вложеного поля. Пример '#{id} - {name}' */
  public $relatedFieldLabelMask = 'related field label';
  /** @var string */
  public $mapperName;
  /** @var MapperData */
  public $mapperData;
  /** @var array поля которые необходимо вернуть */
  public $fields = [];
  /** @var array дополнительные данные которые необходимо вернуть */
  public $customFields = [];
  /** @var string|array форматтер для кастомного значения @see /web/admin/js/common.js */
  public $customFieldFormatter = '';
  /** @var string|array форматтер для вложенного кастомного значения @see /web/admin/js/common.js  */
  public $relatedCustomFieldFormatter = '';
  /**
   * @var array Поля, по которым необходимо производить сортировку
   * Пример: ['totalRevenue' => SORT_DESC, 'sources' => ['totalRevenue' => SORT_DESC]]
   */
  public $orderFields = [];

  public $items;

  /**
   * @var string
   */
  protected $_id;

  public function init()
  {
    self::$count++;

    if ($this->url === null && $this->mapperName) {
      $mapperName = Inflector::camel2id($this->mapperName, '-');
      $this->url = Url::to(["/api/cf/{$mapperName}/"]);
    }

    if ($this->mapperName) {
      $this->mapperData = new MapperData([
        'fields' => $this->fields,
        'searchFields' => $this->searchFields,
        'limit' => $this->limit,
        'filters' => $this->getInitFilters(),
        'customFields' => $this->customFields,
        'orderFields' => $this->orderFields,
        'depth' => 2,
      ]);
    }

    parent::init();
  }

  /**
   * @return array
   */
  public function getInitFilters()
  {
    return Yii::$app->request->get($this->formName) ?: [];
  }

  /**
   * @return string
   * @throws \yii\base\InvalidConfigException
   */
  public function run()
  {
    $this->registerJs();
    return $this->render('complex-filter', [
      'id' => $this->id,
      'label' => $this->label,
    ]);
  }

  /**
   * @inheritdoc
   */
  public function getId($autoGenerate = true)
  {
    if ($autoGenerate && $this->_id === null) {
      $this->_id = 'complex-filter-' . self::$count;
    }

    return $this->_id;
  }

  /**
   * @throws \yii\base\InvalidConfigException
   */
  protected function registerJs()
  {
    $count = self::$count;

    $config = Json::encode($this->buildConfig());
    $widgetId = $this->getId();
    $searchInputId = $this->getSearchInputId();
    $searchButtonId = $this->getSearchButtonId();

    $js = <<<JS
      cFilterObject$count = new cFilter();
      cFilterObject$count.init($config);
JS;
    // TODO: Не получилось перенести этот код в ассет, тк при использовании нескольких экземпляров сиджета события неверно навешиваются
    // TODO: Попытаться разобраться
    if ($this->isAjax) {
      $js .= <<<JS
      // Ajax-пагинация
      $(document).on('click', '#$widgetId' + ' .' + CF_NEXTPAGE_CLASSNAME + ' a:not(.' + CF_NEXTPAGE_BLOCKED_CLASSNAME + ')', function (e) {
        $(this).addClass(CF_NEXTPAGE_BLOCKED_CLASSNAME);
        $.get($(this).data('url'), {
            'fields': cFilterObject$count.config.fields.join(','),
            'custom_fields': cFilterObject$count.config.customFields.join(','),
            'search_fields': cFilterObject$count.config.searchFields.join(','),
            'order_fields': cFilterObject$count.config.orderFields.join(','),
            'limit': cFilterObject$count.config.limit,
            'offset': 0,
            'depth': 2,
          })
          .success(function (data) {
            cFilterObject$count.addData(data);
          });
        return false;
      });
      // по клику на кнопку
      $(document).on('click', '#$searchButtonId', function () {
        cFilterObject$count.ajaxSearch(true);
        return false;
      });
      // по нажатию enter
      $(document).on('keypress', '#$searchInputId', function (e) {
        if(e.which === 13) {
          cFilterObject$count.ajaxSearch(true);
          return false;
        }
      });
JS;
    }
    if (!$this->isAjax) {
      $js .= <<<JS
      // JS-поиск
      // После секунды набора
      $(document).on('keyup', '#$searchInputId', function(e) {
        if (e.which === 13) {
          return;
        }
        
        query = $(this).val();
        cFilterObject$count.delay(function () {
          // Если в эту секунду уже успели нажать Enter, не надо искать
          if (cFilterObject$count.getLastSearchQuery() != query) {
            cFilterObject$count.jsSearch(query, true);
          }
        }, 1000);
      });
      // по клику на кнопку
      $(document).on('click', '#$searchButtonId', function() {
        cFilterObject$count.jsSearch($('#$searchInputId').val(), true);
      });
      // по нажатию enter
      $(document).on('keypress', '#$searchInputId', function(e) {
        if(e.which === 13) {
          cFilterObject$count.jsSearch($(this).val(), true);
          return false;
        }
      });
JS;
    }

    $this->getView()->registerJs($js);
    ComplexFilterAsset::register($this->getView());
  }

  /**
   * @return array
   * @throws \yii\base\InvalidConfigException
   */
  protected function buildConfig()
  {
    return [
      'data' => $this->getData(),
      'widgetId' => $this->getId(),
      'searchInputId' => $this->getSearchInputId(),
      'searchButtonId' => $this->getSearchButtonId(),
      'formName' => $this->formName,
      'fieldName' => $this->fieldName,
      'relatedFieldName' => $this->relatedFieldName,
      'formFieldName' => $this->formName . "[{$this->fieldName}][]",
      'relatedFormFieldName' => $this->formName . "[{$this->relatedFieldName}][]",
      'fieldLabelMask' => $this->fieldLabelMask,
      'relatedFieldLabelMask' => $this->relatedFieldLabelMask,
      'isAjax' => $this->isAjax,
      'searchUrl' => $this->url,
      'fields' => $this->getFieldsArray($this->fields),
      'searchFields' => $this->getFieldsArray($this->searchFields),
      'customFields' => $this->getFieldsArray($this->customFields),
      'customFieldFormatter' => $this->customFieldFormatter,
      'relatedCustomFieldFormatter' => $this->relatedCustomFieldFormatter,
      'orderFields' => $this->getOrderFieldsArray($this->orderFields),
      'limit' => $this->limit,
      'relatedLimit' => $this->relatedLimit
    ];
  }

  /**
   * Получение начальных данных для фильтров
   * @return array
   */
  protected function getData()
  {
    if ($this->items !== null) {
      return $this->items;
    }

    if ($this->mapperName) {
      return $this->items = $this->buildMapper()->getMappedResult();
    }

    return [];
  }

  /**
   * @return BaseMapper
   */
  protected function buildMapper()
  {
    $parser = new MapperDataParser($this->mapperData);

    return (new MapperBuilder($this->mapperName))->build($parser);
  }

  /**
   * @return string
   */
  protected function getSearchInputId()
  {
    return implode('-', [$this->getId(), 'search']);
  }

  /**
   * @return string
   */
  protected function getSearchButtonId()
  {
    return implode('-', [$this->getId(), 'search-button']);
  }

  /**
   * @inheritdoc
   */
  public function getViewPath()
  {
    $class = new ReflectionClass(self::class);
    return dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views';
  }

  /**
   * @param array|string $fields
   * return string
   * @param string $prefix
   * @return array|string
   */
  protected function implodeFields($fields, $prefix = '')
  {
    if (!is_array($fields)) {
      return $fields;
    }

    $result = [];

    foreach($fields as $name => $field) {
      if (!is_array($field)) {
        $result[] = $prefix . $field;
        continue;
      }

      $result[] = $this->implodeFields($field, $prefix . $name . HttpQueryParser::RELATED_FIELDS_DELIMITER);
    }

    return implode(',', $result);
  }

  /**
   * @param $fields
   * @return array
   */
  protected function getFieldsArray($fields)
  {
    $result = $this->implodeFields($fields);

    if ($result === '') {
      return [];
    }

    return explode(',', $result);
  }

  /**
   * Преобразуем orderBy к виду ['-totalRevenue', '-operators__totalRevenue']]
   * @param $orderFields
   * @param string $prefix
   * @return array
   */
  private function getOrderFieldsArray($orderFields, $prefix = '')
  {
    if (!$orderFields) {
      return [];
    }

    $fields = [];
    foreach ($orderFields as $orderField => $orderDirection) {
      if (is_array($orderDirection)) {
        $fields = ArrayHelper::merge(
          $fields,
          $this->getOrderFieldsArray($orderDirection, $prefix . $orderField . HttpQueryParser::RELATED_FIELDS_DELIMITER)
        );
        continue;
      }
      $fields[] = ($orderDirection === SORT_ASC ? '' : '-') . $prefix . $orderField;
    }

    return $fields;
  }
}
