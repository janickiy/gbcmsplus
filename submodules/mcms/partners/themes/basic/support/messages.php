<?php
use yii\widgets\Pjax;
use mcms\partners\components\widgets\TicketWidget;
use mcms\partners\components\widgets\TicketTextWidget;
use mcms\partners\components\widgets\TicketCreateMessageWidget;

?>

<?php Pjax::begin([
  'id' => TicketWidget::PJAX_ID_PREFIX . $model->id,
]); ?>

<?php foreach($model->getText()->each() as $text): ?>
  <?= TicketTextWidget::widget(['model' => $text]); ?>
<?php endforeach ?>

<?= TicketCreateMessageWidget::widget(['ticket' => $model]); ?>

<?php Pjax::end(); ?>
