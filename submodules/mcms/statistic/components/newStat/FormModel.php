<?php

namespace mcms\statistic\components\newStat;

use mcms\common\helpers\ArrayHelper;
use mcms\common\validators\DateCompareValidator;
use mcms\promo\models\Provider;
use mcms\statistic\components\CheckPermissions;
use mcms\statistic\components\DatePeriod;
use mcms\statistic\components\Formattable;
use mcms\statistic\Module;
use Yii;
use yii\base\Model;

/**
 * Модель для фильтрации основной статистики
 *
 * @property int $defaultDaysInterval
 * @property int|int[] $sources
 * @property string $currency
 * @property int $viewerId
 * @property string $dateFrom Y-m-d
 * @property string $dateTo Y-m-d
 */
class FormModel extends Model implements Formattable
{
  const SELECT_ALL = 'total';
  const SELECT_REVSHARE = 'revshare';
  const SELECT_CPA = 'cpa';
  const SELECT_OTP = 'otp';

  const DEFAULT_CURRENCY = 'rub';
  const DATE_RANGE_DELIMITER = ' - ';

  const SCENARIO_EXPORT = 'export';
  const DEFAULT_PAGE_SIZE = 1000;
  /**
   * Тип трафика (total, cpa, revshare, otp)
   * @var string
   */
  public $trafficType;
  /**
   * Ключи группировки
   * @see \mcms\statistic\components\newStat\Group
   * @var string[]
   */
  public $groups;
  /**
   * Ключ второй группировки
   * @see \mcms\statistic\components\newStat\Group
   * @var string
   */
  public $secondGroup;
  /**
   * Фильтрация дат по названию, например "неделя", "сегодня", "вчера".
   * Значения могут быть такими: @see DatePeriod
   * @var string
   */
  public $forceDatePeriod;
  /**
   * фильтр
   * @var int|int[]
   */
  public $landingPayTypes;
  /**
   * фильтр
   * @var int|int[]
   */
  public $providers;
  /**
   * фильтр
   * @var int|int[]
   */
  public $users;
  /**
   * фильтр
   * @var int|int[]
   */
  public $streams;
  /**
   * фильтр
   * @var int|int[]
   */
  public $landings;
  /**
   * фильтр
   * @var int|int[]
   */
  public $landingCategories;
  /**
   * фильтр
   * @var int|int[]
   */
  public $offerCategories;
  /**
   * фильтр
   * @var int|int[]
   */
  public $platforms;
  /**
   * фильтр
   * 0 - нефейк, 1 - фейк.
   * Если пустой массив или [0,1], то надо показать все пдп
   * Если [0] то только НЕ фейки
   * Если [1] то только фейки
   * @var array
   */
  public $isFake;
  /**
   * фильтр
   * @var int|int[]
   */
  public $countries;
  /**
   * фильтр
   * @var int|int[]
   */
  public $operators;

  /**
   * фильтр для LTV столбцов (типа когорты, ARPU, Alive Subs)
   * @var string Y-m-d
   */
  public $ltvDateTo;

  /**
   * Фильтр по менеджерам
   * @var int
   */
  public $manager;

  /**
   * фильтр по часу
   * @var int
   */
  public $hour;

  /**
   * Количество знаков после запятой в значениях полей статы
   * @var int
   */
  public $decimals = 2;
  /**
   * фильтр по сабайди1
   * @var string|string[]
   */
  public $subid1;
  /**
   * фильтр по сабайди2
   * @var string|string[]
   */
  public $subid2;
  /**
   * фильтр по провайдерам
   * @var int
   */
  public $isNoRgk;

  /**
   * фильтр
   * @var string rub|usd|eur
   */
  protected $_currency;
  /**
   * Можно не указывать, тогда по-умолчанию подставится -6days
   * @var string Y-m-d
   */
  private $_dateFrom;
  /**
   * Можно не указывать, тогда по-умолчанию подставится today
   * @var string Y-m-d
   */
  private $_dateTo;
  /**
   * @var string Y-m-d - Y-m-d
   */
  private $_dateRange;
  /**
   * Для кого показываем стату
   * tricky лучше не пихать этот аттрибут в safe rules(). Опасно как-то
   * @var int
   */
  private $_viewerId;
  /**
   * Объект для проверки пермишенов для статы
   * @var CheckPermissions
   */
  private $_permissionsChecker;
  /**
   * @var int количество дней, за которое будет отображаться статистика по дефолту
   */
  private $_defaultDaysInterval = 6;
  /**
   * фильтр
   * @var int|int[]
   */
  private $_sources;


  public function init()
  {
    parent::init();
    if ($this->trafficType === null) {
      $this->trafficType = self::SELECT_ALL;
    }
  }

  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_EXPORT => array_keys($this->getAttributes()),
    ]);
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    // TODO валидация group и фильтров. Уже есть проверки в Fetch, тут на всякий случай можно сделать потом
    return [
      [['groups', 'secondGroup', 'manager', 'hour', 'dateFrom', 'dateTo', 'dateRange', 'forceDatePeriod', 'landingPayTypes', 'providers',
        'users', 'streams', 'sources', 'landings', 'landingCategories', 'offerCategories', 'platforms',
        'isFake', 'isNoRgk', 'currency', 'countries', 'operators', 'ltvDateTo', 'decimals','subid1', 'subid2'], 'safe'],
      ['ltvDateTo', DateCompareValidator::class, 'dateRange' => 'dateRange'],
      // Чистим группировки от некорректных значений
      [['groups'], 'filter', 'filter' => function ($value) {
        $availableGroups = Group::getAvailableGroups();
        return array_intersect($value, $availableGroups);
      }],
      // Чистим вторую группировку от некорректных значений и оставлем её только для экспорта
      [['secondGroup'], 'filter', 'filter' => function () {
        $availableGroups = Group::getAvailableGroups();
        $value = Yii::$app->request->post('secondGroup');
        return in_array($value, $availableGroups, true) ? $value : null;
      }, 'on' => self::SCENARIO_EXPORT],
      ['trafficType', 'in', 'range' => [self::SELECT_REVSHARE, self::SELECT_CPA, self::SELECT_OTP, self::SELECT_ALL], 'strict' => true],
    ];
  }

  /**
   * Сопоставление полей для фильтрации с группировками
   * @return array [Код группировки => код поля для фильтрации]
   */
  protected function getSupportedGroupFields()
  {
    return [
      Group::BY_HOURS => 'hour',
      Group::BY_DATES => 'dateRange',
      Group::BY_MONTH_NUMBERS => 'dateRange',
      Group::BY_WEEK_NUMBERS => 'dateRange',
      Group::BY_LANDINGS => 'landings',
      Group::BY_WEBMASTER_SOURCES => 'sources',
      Group::BY_LINKS => 'sources',
      Group::BY_STREAMS => 'streams',
      Group::BY_PLATFORMS => 'platforms',
      Group::BY_OPERATORS => 'operators',
      Group::BY_COUNTRIES => 'countries',
      Group::BY_PROVIDERS => 'providers',
      Group::BY_USERS => 'users',
      Group::BY_LANDING_PAY_TYPES => 'landing_pay_types',
      Group::BY_MANAGERS => 'manager',
      Group::BY_SUBID_1 => 'subid1',
      Group::BY_SUBID_2 => 'subid2',
    ];
  }

  /**
   * Получить поле для поиска по текущей группировке
   * @return string
   */
  public function getSearchFieldByGroup()
  {
    $group = reset($this->groups);
    return ArrayHelper::getValue($this->getSupportedGroupFields(), $group);
  }

  /**
   * @return string
   * @throws \yii\base\InvalidConfigException
   */
  public function getDateTo()
  {
    if (!empty($this->getDateRange())) {
      list( , $this->_dateTo) = explode(self::DATE_RANGE_DELIMITER, $this->getDateRange());
    }
    if (empty($this->_dateTo)) {
      $this->_dateTo = Yii::$app->formatter->asDate(time(), 'php:Y-m-d');
    }

    return $this->_dateTo;
  }

  /**
   * @param $value
   */
  public function setDateTo($value)
  {
    $this->_dateTo = $value;
  }

  /**
   * @return string
   * @throws \yii\base\InvalidConfigException
   */
  public function getDateFrom()
  {
    if (!empty($this->getDateRange())) {
      list($this->_dateFrom, ) = explode(self::DATE_RANGE_DELIMITER, $this->getDateRange());
    }
    if (empty($this->_dateFrom)) {
      $this->_dateFrom = Yii::$app->formatter->asDate(strtotime("-{$this->defaultDaysInterval} days"), 'php:Y-m-d');
    }
    /** @var Module $module */
    $module = Yii::$app->getModule('statistic');
    if (!$module->canViewFullTimeStatistic()) {
      $minTime = strtotime('-3 months');
      $this->_dateFrom = strtotime($this->_dateFrom) < $minTime
        ? Yii::$app->formatter->asDate($minTime, 'php:Y-m-d')
        : $this->_dateFrom;
    }

    return $this->_dateFrom;
  }

  /**
   * @param $value
   */
  public function setDateFrom($value)
  {
    $this->_dateFrom = $value;
  }

  /**
   * @param string $value
   */
  public function setDateRange($value)
  {
    $this->_dateRange = $value;
  }

  /**
   * @return string
   */
  public function getDateRange()
  {
    if (!empty($this->forceDatePeriod)) {
      $periodDates = DatePeriod::getPeriodDates($this->forceDatePeriod);
      $this->_dateRange = $periodDates['from'] . self::DATE_RANGE_DELIMITER . $periodDates['to'];
    }
    return $this->_dateRange;
  }

  /**
   * @return CheckPermissions
   */
  public function getPermissionsChecker()
  {
    if (!$this->_permissionsChecker) {
      $this->_permissionsChecker = Yii::createObject([
        'class' => CheckPermissions::class,
        'viewerId' => $this->getViewerId(),
      ]);
    }

    return $this->_permissionsChecker;
  }

  /**
   * @return array
   */
  public function getFormatterParams()
  {
    return [
      'decimals' => (int)$this->decimals,
    ];
  }

  /**
   * @return int|string
   */
  public function getViewerId()
  {
    $this->_viewerId = $this->_viewerId ?: Yii::$app->user->id;
    return $this->_viewerId;
  }

  /**
   * @param $value
   */
  public function setViewerId($value)
  {
    $this->_viewerId = (int)$value;
  }

  /**
   * @return string
   */
  public function getCurrency()
  {
    if (!isset($this->_currency)) {
      $this->_currency = self::DEFAULT_CURRENCY;
    }
    return $this->_currency;
  }

  /**
   * @param $value
   */
  public function setCurrency($value)
  {
    // TRICKY: Если передали NULL, преобразуем в false, чтобы isset($this->_currency) возвращало true. @see getCurrency()
    $value = $value ?: false;
    $this->_currency = $value;
  }

  /**
   * @return array|int|\int[]
   */
  public function getProviders()
  {
    if (!$this->isNoRgk) {
      return $this->providers;
    }

    $noRgkProviders = Provider::find()->select('id')->andWhere(['is_rgk' => 0])->column();

    return $this->providers ? array_intersect($noRgkProviders, (array)$this->providers) : $noRgkProviders;
  }

  /**
   * @inheritdoc
   */
  public function getAttributeLabel($attribute)
  {
    return Yii::_t('statistic.new_statistic_refactored.filter-' . $attribute);
  }

  /**
   * @param int $value
   */
  public function setDefaultDaysInterval($value)
  {
    $this->_defaultDaysInterval = (int)$value;
  }

  /**
   * @return int
   */
  public function getDefaultDaysInterval()
  {
    return $this->_defaultDaysInterval;
  }

  /**
   * @return int[]
   */
  public function getSources()
  {
    return $this->_sources ? (array)$this->_sources : [];
  }

  /**
   * @param int|int[] $sources
   */
  public function setSources($sources)
  {
    $this->_sources = $sources;
  }

  /**
   * есть ли группировка по указанному полю
   * @param string|array $groups
   * @return bool
   */
  public function hasGroupBy($groups)
  {
    if (!is_array($groups)) {
      $groups = [$groups];
    }

    return !empty(array_intersect($this->groups, $groups));
  }

  /**
   * приводит данные из вида [1 => 'Name'] к виду ['id' => 1, 'name' => 'Name']
   * @param array $items
   * @return array
   */
  public function toMap($items)
  {
    array_walk($items, function (&$a, $b) {
      $a = ['id' => $b, 'name' => $a];
    });

    return array_values($items);
  }
}
