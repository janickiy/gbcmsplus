<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m200409_144131_alert_event_check_interval extends Migration
{
    use PermissionTrait;

    public function up()
    {
        $this->addColumn('alert_events', 'check_interval', 'INT(10) UNSIGNED NOT NULL DEFAULT 0');
        $this->addColumn('alert_event_logs', 'model_id', 'MEDIUMINT(10) UNSIGNED AFTER event_id');
    }

    public function down()
    {
        $this->dropColumn('alert_events', 'check_interval');
        $this->dropColumn('alert_event_logs', 'model_id');
    }
}
