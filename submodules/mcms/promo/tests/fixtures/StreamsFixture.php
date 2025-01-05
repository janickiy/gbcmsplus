<?php

namespace mcms\promo\tests\fixtures;

use mcms\common\traits\FixtureTrait;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class StreamsFixture
 * @package mcms\promo\tests\fixtures
 */
class StreamsFixture extends ActiveFixture
{

  use FixtureTrait;

  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\promo\models\Stream';


  /**
   * @inheritdoc
   */
  public $depends = ['users.users'];

  /**
   * https://github.com/yiisoft/yii2/issues/5442
   * @inheritdoc
   */
  public function beforeLoad() {
    parent::beforeLoad();
    $this->db->createCommand()->setSql('SET FOREIGN_KEY_CHECKS = 0')->execute();
  }
  /**
   * https://github.com/yiisoft/yii2/issues/5442
   * @inheritdoc
   */
  public function afterLoad() {
    parent::afterLoad();
    $this->db->createCommand()->setSql('SET FOREIGN_KEY_CHECKS = 1')->execute();
  }
}