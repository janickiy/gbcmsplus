<?php

namespace mcms\statistic\components\mainStat;

use mcms\statistic\components\CheckPermissions;
use mcms\statistic\components\DatePeriod;
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
class FormModel extends Model
{
  const SELECT_ALL = '';
  const SELECT_REVSHARE = 'revshare';
  const SELECT_CPA = 'cpa';

  /**
   * Ключи группировки
   * @see \mcms\statistic\components\mainStat\Group
   * @var string[]
   */
  public $groups;
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
   * Для кого показываем стату
   * tricky лучше не пихать этот аттрибут в safe rules(). Опасно как-то
   * @var int
   */
  private $_viewerId;
  /**
   * Селектор ЦПА/Ревшар (используется только в ПП). ЦПА - это продажи и ИК
   * @var string self::SELECT_REVSHARE|self::SELECT_CPA
   */
  public $revshareOrCpa;
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

  /**
   * @inheritdoc
   */
  public function rules()
  {
    // TODO валидация group и фильтров. Уже есть проверки в Fetch, тут на всякий случай можно сделать потом
    return [
      [['groups', 'dateFrom', 'dateTo', 'forceDatePeriod', 'landingPayTypes', 'providers', 'users', 'streams', 'sources', 'landings', 'landingCategories', 'platforms', 'isFake', 'currency', 'countries', 'operators', 'revshareOrCpa'], 'safe'],
      // Чистим группировки от некорректных значений
      [['groups'], 'filter', 'filter' => function ($value) {
        $availableGroups = Group::getAvailableGroups();
        return array_intersect($value, $availableGroups);
      }],
      ['revshareOrCpa', 'in', 'range' => [self::SELECT_REVSHARE, self::SELECT_CPA], 'strict' => true],
    ];
  }

  /**
   * @return string
   */
  public function getDateTo()
  {
    if (!empty($this->forceDatePeriod)) {
      $periodDates = DatePeriod::getPeriodDates($this->forceDatePeriod);
      $this->_dateTo = $periodDates['to'];
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
   */
  public function getDateFrom()
  {
    if (!empty($this->forceDatePeriod)) {
      $periodDates = DatePeriod::getPeriodDates($this->forceDatePeriod);
      $this->_dateFrom = $periodDates['from'];
    }
    if (empty($this->_dateFrom)) {
      $this->_dateFrom = Yii::$app->formatter->asDate(strtotime("-{$this->defaultDaysInterval} days"), 'php:Y-m-d');
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
   * @return bool
   */
  public function isCPA()
  {
    return $this->revshareOrCpa === self::SELECT_CPA;
  }

  /**
   * @return bool
   */
  public function isRevshare()
  {
    return $this->revshareOrCpa === self::SELECT_REVSHARE;
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
      $this->_currency = Yii::$app->getModule('promo')->api('mainCurrenciesWidget')->getSelectedCurrency();
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
   * @inheritdoc
   */
  public function getAttributeLabel($attribute)
  {
    return Yii::_t('statistic.main_statistic_refactored.filter-' . $attribute);
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
}
