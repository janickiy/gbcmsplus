<?php

use console\components\Migration;

/**
 * Handles adding columns to table `{{%operators}}`.
 */
class m241017_001731_add_is_geo_default_column_to_operators_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%operators}}', 'is_geo_default', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%operators}}', 'is_geo_default');
    }
}
