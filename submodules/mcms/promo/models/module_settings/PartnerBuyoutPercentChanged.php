<?php


namespace mcms\promo\models\module_settings;

use Yii;

class PartnerBuyoutPercentChanged extends yii\base\Model
{

  public $oldBuyoutPercent;
  public $newBuyoutPercent;

  public function getReplacements()
  {
    return [
      'old_buyout_percent' => [
        'value' => $this->oldBuyoutPercent,
        'help' => [
          'label' => Yii::_t('promo.settings.replacement-partner-old_buyout_percent')
        ]
      ],
      'new_buyout_percent' => [
        'value' => $this->newBuyoutPercent,
        'help' => [
          'label' => Yii::_t('promo.settings.replacement-partner-new_buyout_percent')
        ]
      ],
    ];
  }

}