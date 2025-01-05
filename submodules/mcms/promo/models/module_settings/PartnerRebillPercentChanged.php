<?php


namespace mcms\promo\models\module_settings;

use Yii;

class PartnerRebillPercentChanged extends yii\base\Model
{

  public $oldRebillPercent;
  public $newRebillPercent;

  public function getReplacements()
  {
    return [
      'old_rebill_percent' => [
        'value' => $this->oldRebillPercent,
        'help' => [
          'label' => Yii::_t('promo.settings.replacement-partner-old_rebill_percent')
        ]
      ],
      'new_rebill_percent' => [
        'value' => $this->newRebillPercent,
        'help' => [
          'label' => Yii::_t('promo.settings.replacement-partner-new_rebill_percent')
        ]
      ],
    ];
  }

}