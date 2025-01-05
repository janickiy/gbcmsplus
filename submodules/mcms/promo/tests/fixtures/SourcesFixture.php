<?php

namespace mcms\promo\tests\fixtures;

use yii\test\ActiveFixture;
use Yii;

/**
 * Class SourcesFixture
 * @package mcms\promo\tests\fixtures
 */
class SourcesFixture extends ActiveFixture
{
  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\promo\models\Source';


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