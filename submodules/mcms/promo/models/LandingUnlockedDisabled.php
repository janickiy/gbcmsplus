<?php

namespace mcms\promo\models;

use Yii;

class LandingUnlockedDisabled extends Landing
{
  public function getReplacements()
  {
    return array_merge(parent::getReplacements(), [
      'unblock_request' => [
        'value' => $this->getReplacementUnblockRequest(),
        'help' => [
          'label' => Yii::_t('promo.replacements.landing_unblock_request'),
          'class' => LandingUnblockRequest::class
        ]
      ]
    ]);
  }
}
