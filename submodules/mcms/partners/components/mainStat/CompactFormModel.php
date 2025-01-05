<?php

namespace mcms\partners\components\mainStat;

/**
 * Модель для фильтрации краткой статы партнера
 */
class CompactFormModel extends FormModel
{
  /**
   * @inheritdoc
   */
  public function rules()
  {
    // заглушим, т.к. пока фильтров нет никаких, но не разрешает даты более 6мес указать.
    return [];
  }
}
