<?php

namespace mcms\loyalty\widgets;

use mcms\loyalty\models\LoyaltyBonus;
use rgk\utils\widgets\ActiveDropdown;

/**
 * Виджет-дропдаун статусов бонуса.
 * Пример использования
 * ```
 * BonusStatusesDropdown::widget([
 * 'prompt' => '',
 * 'attribute' => 'status',
 * 'searchModel' => $searchModel
 * ])
 * ```
 */
class BonusStatusesDropdown extends ActiveDropdown
{
  /**
   * @var string
   */
  public $prompt = '';

  public function init()
  {
    parent::init();
    $this->options['prompt'] = $this->prompt;
    $this->source = LoyaltyBonus::statusNameList();
  }
}