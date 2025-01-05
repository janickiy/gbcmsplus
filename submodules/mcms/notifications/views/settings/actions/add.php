<?php
use mcms\common\helpers\Link;
?>
<?= Link::get('/notifications/settings/add', ['id' => $model->id], ['class' => 'btn btn-success'], '<i class="glyphicon glyphicon-plus"></i> ' . Yii::_t('main.add_action')) ?>


