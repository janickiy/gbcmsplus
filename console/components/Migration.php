<?php

namespace console\components;

/**
 */
class Migration extends \yii\db\Migration
{

    /**
     * Чтобы опции были автоматические и не пришлось потом чинить кодировку таблицы
     * @param string $table
     * @param array $columns
     * @param null $options
     */
    public function createTable($table, $columns, $options = null)
    {
        if ($this->db->driverName === 'mysql' && empty($options)) {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $options = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        parent::createTable($table, $columns, $options);
    }
}
