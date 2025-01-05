<?php

/* @var $this yii\web\View */
/* @var $model mcms\logs\models\LogsSearch */

?>

<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <h4 class="modal-title"><?=Yii::_t('logs.main.full-info')?></h4>
</div>
<div class="modal-body">
  <?php
  $json = \yii\helpers\Json::decode($model->EventData);
  echo \yii\helpers\VarDumper::dumpAsString($json,5,true);
  ?>
</div>
