<?php
use mcms\common\helpers\Link;

?>

<?= Link::get('/promo/trafficback-providers/update', ['id' => $model->id], ['class' => 'btn btn-warning'], '<i class="glyphicon glyphicon-pencil"></i> ' . Yii::_t('promo.trafficback_providers.update')); ?>