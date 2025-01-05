<?php

namespace mcms\promo\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class PartnerProgramItemFixture
 * @package mcms\promo\tests\fixtures
 */
class PartnerProgramItemFixture extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\promo\models\PartnerProgramItem';

  /**
   * @inheritdoc
   */
  public $depends = ['promo.partner_programs'];
}