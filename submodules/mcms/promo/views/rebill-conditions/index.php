<?php
use mcms\promo\components\widgets\RebillConditionsWidget;
?>

<?php $this->beginBlock('actions'); ?>
<?= $this->render('_create_button', ['partnerId' => null]) ?>
<?php $this->endBlock() ?>

<?= RebillConditionsWidget::widget([
  'options' => [
    'renderCreateButton' => false,
  ]
]); ?>