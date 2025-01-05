<?php
use mcms\common\helpers\Link;
?>
<?= Link::get('/support/tickets/open/', ['id' => $model->id], ['class' => 'btn btn-success'],  Yii::_t('support.controller.open_ticket')) ?>