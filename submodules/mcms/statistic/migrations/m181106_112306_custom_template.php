<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m181106_112306_custom_template extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    // Создание прав для нового контроллера
    $this->createPermission('StatisticNewColumnTemplatesController', 'Контроллер ColumnTemplates', 'StatisticModule');
    $this->createPermission('StatisticNewColumnTemplatesCreate', 'Создание шаблона для столбцов таблицы', 'StatisticNewColumnTemplatesController', ['admin', 'root']);
    $this->createPermission('StatisticNewColumnTemplatesUpdate', 'Изменение шаблона для столбцов таблицы', 'StatisticNewColumnTemplatesController', ['admin', 'root']);
    $this->createPermission('StatisticNewColumnTemplatesDelete', 'Удаление шаблона для столбцов таблицы', 'StatisticNewColumnTemplatesController', ['admin', 'root']);
    $this->createPermission('StatisticNewColumnTemplatesGetTemplate', 'Получение списка шаблонов', 'StatisticNewColumnTemplatesController', ['admin', 'root']);
  }

  /**
  */
  public function down()
  {
    $this->removePermission('StatisticNewColumnTemplatesController');
    $this->removePermission('StatisticNewColumnTemplatesCreate');
    $this->removePermission('StatisticNewColumnTemplatesUpdate');
    $this->removePermission('StatisticNewColumnTemplatesDelete');
    $this->removePermission('StatisticNewColumnTemplatesGetTemplate');
  }
}
