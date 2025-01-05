<?php
use mcms\common\helpers\Link;
?>
<?= Link::get('/support/tickets/close/', ['id' => $model->id], ['class' => 'btn btn-success'],  Yii::_t('support.controller.close_ticket')) ?>