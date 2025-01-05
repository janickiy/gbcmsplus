<?php

namespace mcms\promo\models;

use mcms\common\models\AbstractMassModel;
use mcms\common\traits\Translate;
use mcms\promo\Module;

/**
 *
 * @property string $rebill_percent
 * @property string $buyout_percent
 * @property string $cpa_profit_rub
 * @property string $cpa_profit_eur
 * @property string $cpa_profit_usd
 */
class PartnerProgramItemMassModel extends AbstractMassModel
{
  use Translate;

  public $buyout_percent;
  public $rebill_percent;
  public $cpa_profit_rub;
  public $cpa_profit_usd;
  public $cpa_profit_eur;

  const LANG_PREFIX = 'promo.partner_program_items.';

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'rebill_percent',
      'buyout_percent',
      'cpa_profit_rub',
      'cpa_profit_usd',
      'cpa_profit_eur',
    ]);
  }

  /**
   * @inheritdoc
   */
  public function ownFields()
  {

    $fields = [
      'rebill_percent' => 'rebill_percent',
      'buyout_percent' => 'buyout_percent',
    ];

    if (PersonalProfit::canManagePersonalCPAPrice()) {
      $fields += [
        'cpa_profit_rub' => 'cpa_profit_rub',
        'cpa_profit_usd' => 'cpa_profit_usd',
        'cpa_profit_eur' => 'cpa_profit_eur',
      ];
    }
    return $fields;
  }
}
