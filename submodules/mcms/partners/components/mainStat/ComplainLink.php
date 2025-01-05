<?php

namespace mcms\partners\components\mainStat;

use mcms\common\helpers\Html;
use Yii;
use mcms\statistic\components\mainStat\Group;
use mcms\statistic\components\mainStat\mysql\CellLink;

/**
 * Создаем ссылку на детальную стату по жалобам
 */
class ComplainLink extends CellLink
{

  public $searchModelClass = FormModel::class;

  /**
   * @param Row $row
   * @return ComplainLink
   */
  public static function create(Row $row)
  {
    return new self($row);
  }

  /**
   * @param $count
   * @return string
   */
  protected function toStringInternal($count)
  {
    $urlParams = array_merge($this->getFilterParams(), $this->getGroupParams());

    return Html::a($count, ['complains', 'statistic' => $urlParams], ['data-pjax' => 0]);
  }

  /**
   * @return float|int
   */
  protected function getCellValue()
  {
    return $this->row->getComplains();
  }

  /**
   * @return array [ключ_осн_статы => ключ_статы_жалоб]
   */
  protected function getSupportedFilterFields()
  {
    return [
      'landings' => 'landings',
      'webmasterSources' => 'webmasterSources',
      'arbitraryLinks' => 'arbitraryLinks',
      'operators' => 'operators',
      'platforms' => 'platforms',
      'streams' => 'streams',
      'countries' => 'countries',
    ];
  }

  /**
   * @return array [ключ_осн_статы => ключ_статы_жалоб]
   */
  protected function getSupportedGroupFields()
  {
    return [
      Group::BY_DATES => 'dates',
      Group::BY_LANDINGS => 'landings',
      Group::BY_WEBMASTER_SOURCES => 'webmasterSources',
      Group::BY_LINKS => 'arbitraryLinks',
      Group::BY_STREAMS => 'streams',
      Group::BY_PLATFORMS => 'platforms',
      Group::BY_OPERATORS => 'operators',
      Group::BY_COUNTRIES => 'countries',
    ];
  }

  /**
   * @inheritdoc
   */
  protected function getRequestParams()
  {
    return Yii::$app->request->post('FormModel');
  }
}
