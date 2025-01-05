<?php

namespace mcms\statistic\dbfix;

use console\components\Migration;

/*
 * php yii db-fix/run-command subid_to_search_subscriptions --sm=statistic
 */

class subid_to_search_subscriptions extends Migration
{
  public function up()
  {
    $this->execute('
    UPDATE search_subscriptions ss
      LEFT JOIN hit_params hp ON ss.hit_id = hp.hit_id
      LEFT JOIN subid_glossary sg1 ON hp.subid1 = sg1.value
      LEFT JOIN subid_glossary sg2 ON hp.subid2 = sg2.value
    SET ss.subid1_id = sg1.id, ss.subid2_id = sg2.id;
    ');
  }
}
