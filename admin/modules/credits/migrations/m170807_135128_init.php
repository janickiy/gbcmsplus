<?php

use console\components\Migration;

/**
 * Class m170807_135128_init
 */
class m170807_135128_init extends Migration
{
    use \rgk\utils\traits\PermissionTrait;

    /**
     *
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('credits', [
            'id' => $this->primaryKey(),
            'amount' => $this->decimal(10, 2)->notNull()->unsigned()->comment('сумма кредита'),
            'currency' => $this->string(3)->notNull()->comment('валюта кредита'),
            'status' => $this->smallInteger(1)->notNull()->unsigned(),
            'percent' => $this->decimal(5, 2)->notNull()->unsigned()->comment('процент кредита'),
            'created_at' => $this->integer(10)->notNull(),
            'updated_at' => $this->integer(10)->notNull(),
            'closed_at' => $this->integer(10),
        ], $tableOptions);

        $this->createPermission('CreditsModule', 'Модуль Credits');
        $this->createPermission('CreditsCreditsController', 'Контроллер кредитов', 'CreditsModule');
        $this->createPermission('CreditsCreditsIndex', 'Просмотр кредитов', 'CreditsCreditsController', ['root', 'admin', 'reseller']);

        $this->createTable('credit_transactions', [
            'id' => $this->primaryKey(),
            'credit_id' => $this->integer()->notNull()->unsigned()->comment('ссылка на кредит'),
            'amount' => $this->decimal(10, 2)->notNull()->unsigned()->comment('сумма кредита'),
            'created_at' => $this->integer(10)->notNull(),
            'updated_at' => $this->integer(10)->notNull(),
            'type' => $this->smallInteger(1)->notNull()->unsigned(),
        ], $tableOptions);
    }

    /**
     *
     */
    public function down()
    {
        $this->dropTable('credit_transactions');
        $this->dropTable('credits');

        $this->removePermission('CreditsModule');
        $this->removePermission('CreditsCreditsController');
        $this->removePermission('CreditsCreditsIndex');
    }
}