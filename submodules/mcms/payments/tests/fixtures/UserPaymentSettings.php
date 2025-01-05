<?php

namespace mcms\payments\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class UserPaymentSettings
 * @package mcms\payments\tests\fixtures
 */
class UserPaymentSettings extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\payments\models\UserPaymentSetting';


  /**
   * @inheritdoc
   */
  public $depends = ['users.users'];

  public function afterLoad()
  {
    Yii::$app->cache->flush();

    parent::afterLoad();
  }
}