<?php
use mcms\common\helpers\Link;
?>
<?=Link::get('/promo/smart-links/update', ['id' => $model->id], ['class' => 'btn btn-warning'], '<i class="glyphicon glyphicon-pencil"></i> ' . Yii::_t('promo.arbitrary_sources.update')) ?>
<?=Link::get('/promo/smart-links/index/', ['id' => $model->id], ['class' => 'btn btn-warning'], '<i class="glyphicon glyphicon-list"></i> ' . Yii::_t('promo.landings.main')) ?>


