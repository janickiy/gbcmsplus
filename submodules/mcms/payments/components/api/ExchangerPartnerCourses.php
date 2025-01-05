<?php

namespace mcms\payments\components\api;

use yii\helpers\ArrayHelper;

/**
 * Курсы конвертации с учетом процента партнера указанного в настройках
 */
class ExchangerPartnerCourses extends ExchangerCourses
{
  /**
   * @param array $params
   * @throws \yii\base\InvalidConfigException
   */
  public function init($params = [])
  {
    $this->currencyCourses = $this->getCachedPartnerCurrencyCourses();
  }
}
