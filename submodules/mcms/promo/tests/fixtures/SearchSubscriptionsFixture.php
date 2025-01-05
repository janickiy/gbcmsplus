<?php

namespace mcms\promo\tests\fixtures;

use yii\test\ActiveFixture;
use Yii;

/**
 * Class SubscriptionsFixture
 * @package mcms\promo\tests\fixtures
 */
class SearchSubscriptionsFixture extends ActiveFixture
{
  /**
   * @inheritdoc
   */
  public $tableName = 'search_subscriptions';

  /**
   * @inheritdoc
   */
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