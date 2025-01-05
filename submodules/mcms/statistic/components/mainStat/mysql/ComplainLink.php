<?php

namespace mcms\statistic\components\mainStat\mysql;

use mcms\common\helpers\Html;
use mcms\statistic\components\mainStat\Group;
use mcms\statistic\models\Complain;
use Yii;

/**
 * Создаем ссылку на детальную стату по жалобам
 */
class ComplainLink extends CellLink
{
  /**
   * @var int
   */
  private $complainType;

  /**
   * @see create()
   * @param Row $row
   * @param int $complainType
   * @see Complain::$type
   */
  protected function __construct(Row $row, $complainType)
  {
    $this->complainType = (int)$complainType;
    parent::__construct($row);
  }

  /**
   * имхо удобнее чем конструктор
   * @param Row $row
   * @param int $complainType
   * @see Complain::$type
   * @return ComplainLink
   */
  public static function create(Row $row, $complainType)
  {
    return new self($row, $complainType);
  }

  /**
   * @param $count
   * @return string
   */
  protected function toStringInternal($count)
  {
    $urlParams = array_merge($this->getFilterParams(), $this->getGroupParams());

    $urlParams['type'] = $this->complainType;

    return Html::a($count, ['/statistic/detail/complains', 'statistic' => $urlParams], ['data-pjax' => 0]);
  }

  /**
   * @return float|int
   */
  protected function getCellValue()
  {
    if ($this->complainType === Complain::TYPE_TEXT) {
      return $this->row->getComplains();
    }

    if ($this->complainType === Complain::TYPE_CALL) {
      return $this->row->getCalls();
    }

    if ($this->complainType === Complain::TYPE_CALL_MNO) {
      return $this->row->getCallsMno();
    }

    return 0;
  }

  /**
   * @return array [ключ_осн_статы => ключ_статы_жалоб]
   */
  protected function getSupportedFilterFields()
  {
    return [
      'landings' => 'landings',
      'sources' => 'sources',
      'operators' => 'operators',
      'platforms' => 'platforms',
      'streams' => 'streams',
      'providers' => 'providers',
      'countries' => 'countries',
      'users' => 'users',
      'landingPayTypes' => 'landing_pay_types',
    ];
  }

  /**
   * например по менеджерам не поддерживаем на момент написания коммента
   * @return array [ключ_осн_статы => ключ_статы_жалоб]
   */
  protected function getSupportedGroupFields()
  {
    return [
      // TODO есть мысль общие фильтры со всех страниц вынести в общие константы в стиле Component::FILTER_LANDINGS,
      // с целью чтоб везде одинаково назывались
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
    ];
  }

  /**
   * Получить параметры фильтрации из запроса. Например Yii::$app->request->get('FormModel')
   * @return mixed
   */
  protected function getRequestParams()
  {
    return Yii::$app->request->get('FormModel');
  }
}
