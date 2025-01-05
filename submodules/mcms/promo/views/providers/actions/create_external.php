<?php
use mcms\common\widget\modal\Modal;
use yii\bootstrap\Html;

?>

<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => Html::icon('plus') . ' ' . Yii::_t('promo.providers.create_external'),
    'class' => 'btn btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['/promo/providers/create-external'],
]) ?>
