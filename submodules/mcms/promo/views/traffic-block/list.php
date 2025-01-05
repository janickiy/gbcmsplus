<?php

use mcms\promo\components\widgets\TrafficBlockWidget;
use rgk\utils\widgets\modal\Modal;
use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $searchModel mcms\promo\models\search\TrafficBlockSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>

<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => Html::icon('plus') . ' ' . Yii::_t('promo.traffic_block.create'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['/promo/traffic-block/create-modal/'],
]) ?>
<?php $this->endBlock() ?>

<?= TrafficBlockWidget::widget();
