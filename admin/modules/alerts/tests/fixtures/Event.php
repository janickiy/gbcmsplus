<?php
namespace admin\modules\alerts\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;

class Event extends ActiveFixture
{
  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'admin\modules\alerts\models\Event';
}
