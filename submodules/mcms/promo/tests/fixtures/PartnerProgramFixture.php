<?php

namespace mcms\promo\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class PartnerProgramFixture
 * @package mcms\promo\tests\fixtures
 */
class PartnerProgramFixture extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\promo\models\PartnerProgram';

  /**
   * @inheritdoc
   */
  public $depends = ['promo.operators', 'promo.landings'];
}