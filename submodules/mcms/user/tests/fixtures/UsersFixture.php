<?php

namespace mcms\user\tests\fixtures;

use mcms\common\helpers\ArrayHelper;
use yii\test\ActiveFixture;
use Yii;

/**
 * Class UsersFixture
 * @package mcms\user\tests\fixtures
 */
class UsersFixture extends ActiveFixture
{
  /**
   * @inheritdoc
   */
  public $modelClass = 'mcms\user\models\User';

  /**
   * @inheritdoc
   */
  protected function resetTable()
  {
    $table = $this->getTableSchema();
    $this->db->createCommand()->delete($table->fullName, [
      'NOT IN', 'id', [1, 2, 3, 4, 5]
    ])->execute();
    if ($table->sequenceName !== null) {
      $this->db->createCommand()->resetSequence($table->fullName, 1)->execute();
    }
    $authManager = Yii::$app->authManager;
    foreach ($this->getData() as $alias => $row) {
      $authManager->revokeAll($row['id']);
    }
  }

  /**
   * @inheritdoc
   */
  public function load()
  {
    $this->resetTable();
    $this->data = [];
    $table = $this->getTableSchema();

    $roles = [];
    $authManager = Yii::$app->authManager;

    foreach ($this->getData() as $alias => $row) {
      $userTableRow = $row;
      unset($userTableRow['roles']);

      $primaryKeys = $this->db->schema->insert($table->fullName, $userTableRow);

      /** заполняем роли */
      foreach (ArrayHelper::getValue($row, 'roles') as $roleName) {

        if (!$role = ArrayHelper::getValue($roles, $roleName)) {
          $role = $roles[$roleName] = $authManager->getRole($roleName);
        }

        $authManager->assign($role, current($primaryKeys));
      }

      $this->data[$alias] = array_merge($userTableRow, $primaryKeys);
    }
  }


  public function beforeLoad() {
    parent::beforeLoad();
    $this->db->createCommand()->setSql('SET FOREIGN_KEY_CHECKS = 0')->execute();
  }

  public function afterLoad() {
    parent::afterLoad();
    $this->db->createCommand()->setSql('SET FOREIGN_KEY_CHECKS = 1')->execute();
  }


}