<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m200521_192343_add_email_unsubscribe_token extends Migration
{
    use PermissionTrait;

    /**
     */
    public function up()
    {
        $this->addColumn('users', 'email_unsubscribe_token', $this->string()->after('email_activation_code'));
    }

    /**
     */
    public function down()
    {
        $this->dropColumn('users', 'email_unsubscribe_token');
    }
}
