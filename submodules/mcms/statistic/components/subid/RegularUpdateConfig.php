<?php

namespace mcms\statistic\components\subid;

use mcms\statistic\models\mysql\StatFilter;
use Yii;
use yii\base\Object;

/**
 */
class RegularUpdateConfig extends Object
{
  /**
   * @var string Дата в формате 2017-11-24 по которой фильтруем хиты для генерации подписок.
   * Можно писать текстом, например "-2 days"
   * По-умолчанию "-1 day".
   */
  private $_dateFrom;

  /**
   * @var string Дата в формате 2017-11-24 по которой фильтруем хиты для генерации подписок.
   * Можно писать текстом, например "-2 days"
   * По-умолчанию "today".
   */
  private $_dateTo;

  /**
   * @var int|string Сделать обновление индивидуально какому-то юзеру (или можно указывать через запятую).
   * По-умолчанию не заполнено, будут использованы все таблицы юзеры
   */
  private $_userIds;

  /**
   * @var int[] кэш для юзеров в стат-фильтрах
   */
  private $_statFilterUsers;

  /**
   * @var int время запуска крона, даннные появившиеся после этого времени не будут обрабатываться
   */
  private $_maxTime;

  /**
   * @param $dateFrom
   */
  public function setDateFrom($dateFrom)
  {
    $this->_dateFrom = $dateFrom;
  }

  /**
   * @return string
   * @throws \yii\base\InvalidConfigException
   */
  public function getDateFrom()
  {
    if (!$this->_dateFrom) {
      return Yii::$app->formatter->asDate('-2 days', 'php:Y-m-d');
    }

    return Yii::$app->formatter->asDate($this->_dateFrom, 'php:Y-m-d');
  }

  /**
   * @return int
   */
  public function getTimeFrom()
  {
    return strtotime($this->getDateFrom());
  }

  /**
   * @param $dateTo
   */
  public function setDateTo($dateTo)
  {
    $this->_dateTo = $dateTo;
  }

  /**
   * @return string
   * @throws \yii\base\InvalidConfigException
   */
  public function getDateTo()
  {
    if (!$this->_dateTo) {
      return Yii::$app->formatter->asDate('today', 'php:Y-m-d');
    }

    return Yii::$app->formatter->asDate($this->_dateTo, 'php:Y-m-d');
  }

  /**
   * @return int
   */
  public function getTimeTo()
  {
    return strtotime($this->getDateTo());
  }

  /**
   * @param $time
   */
  public function setMaxTime($time)
  {
    $this->_maxTime = $time;
  }

  /**
   * @return int
   */
  public function getMaxTime()
  {
    return $this->_maxTime;
  }

  /**
   * @param int|string $userIds
   */
  public function setUserIds($userIds)
  {
    $this->_userIds = $userIds;
  }

  /**
   * @return int[] приводим к массиву int
   */
  public function getUserIds()
  {
    if (!$this->_userIds) {
      // достаём из стат-фильтров если не задали юзеров
      if (isset($this->_statFilterUsers)) {
        return $this->_statFilterUsers;
      }

      $this->_statFilterUsers = StatFilter::getUsersIdList();

      return $this->_statFilterUsers;
    }

    if (is_string($this->_userIds)) {
      return array_map('intval', explode(',', $this->_userIds));
    }

    if (is_array($this->_userIds)) {
      return array_map('intval', $this->_userIds);
    }

    return [(int)$this->_userIds];
  }
}
