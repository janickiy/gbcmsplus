<?php

namespace admin\modules\credits\widgets;

use admin\modules\credits\models\CreditTransaction;
use rgk\utils\widgets\ActiveDropdown;

/**
 * Виджет дродаун типов транзакций
 * Пример использования
   ```
   CreditTransactionTypesDropdown::widget([
    'prompt' => '',
    'attribute' => 'status',
    'searchModel' => $searchModel
  ])
  ```
 * Class CreditTransactionTypesDropdown
 * @package admin\modules\payments\components\widgets
 */
class CreditTransactionTypesDropdown extends ActiveDropdown
{
  /**
   * @var string
   */
  public $prompt = '';
  /**
   * @var int[]
   */
  public $exclude = [];

  public function init()
  {
    parent::init();
    $this->options['prompt'] = $this->prompt;
    $this->source = CreditTransaction::typeNameList($this->exclude);
  }
}
