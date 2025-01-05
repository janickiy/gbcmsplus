<?php

namespace mcms\loyalty\widgets;

use mcms\loyalty\models\LoyaltyBonus;
use rgk\utils\widgets\ActiveDropdown;

/**
 * Виджет-дропдаун статусов бонуса.
 * Пример использования
 * ```
 * BonusTypesDropdown::widget([
 * 'prompt' => '',
 * 'attribute' => 'type',
 * 'searchModel' => $searchModel
 * ])
 * ```
 */
class BonusTypesDropdown extends ActiveDropdown
{
  /**
   * @var string
   */
  public $prompt = '';

  public function init()
  {
    parent::init();
    $this->options['prompt'] = $this->prompt;
    $this->source = LoyaltyBonus::typeNameList();
  }
}