<?php

use console\components\Migration;
use mcms\common\helpers\Console;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m181129_092155_new_labels extends Migration
{
  const TABLE = 'hit_params';

  /**
   * @inheritdoc
   */
  public function up()
  {
    if (!Console::confirm('Добавление столбцов в hit_params. Уверены, что хотите продолжить?', true)) {
      return;
    }

    $this->addColumn(self::TABLE, 'subid1', 'varchar(512)');
    $this->addColumn(self::TABLE, 'subid2', 'varchar(512)');
    $this->addColumn(self::TABLE, 'get_params', 'varchar(2048)');
  }

  /**
   * @inheritdoc
   */
  public function down()
  {
    if (!Console::confirm('Удаление столбцов в hit_params. Уверены, что хотите продолжить?', true)) {
      return;
    }

    $this->dropColumn(self::TABLE, 'subid1');
    $this->dropColumn(self::TABLE, 'subid2');
    $this->dropColumn(self::TABLE, 'get_params');
  }
}
