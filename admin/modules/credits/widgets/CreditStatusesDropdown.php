<?php

namespace admin\modules\credits\widgets;

use admin\modules\credits\models\Credit;
use rgk\utils\widgets\ActiveDropdown;

/**
 * Виджет дродаун статусов выплат
 * Пример использования
   ```
   CreditStatusesDropdown::widget([
    'prompt' => '',
    'attribute' => 'status',
    'searchModel' => $searchModel
  ])
  ```
 * Class CreditStatusesDropdown
 * @package admin\modules\payments\components\widgets
 */
class CreditStatusesDropdown extends ActiveDropdown
{
  /**
   * @var string
   */
  public $prompt = '';

  public function init()
  {
    parent::init();
    $this->options['prompt'] = $this->prompt;
    $this->source = Credit::statusNameList();
  }
}
