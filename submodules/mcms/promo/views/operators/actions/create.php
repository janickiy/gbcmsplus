<?php
use mcms\common\helpers\Link;
?>

<?= Link::get('/promo/operators/create', [], ['class' => 'btn btn-success'], '<i class="glyphicon glyphicon-plus"></i> ' . Yii::_t('promo.operators.create'));?>