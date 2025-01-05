<?php
use mcms\partners\components\widgets\TicketWidget;
use mcms\partners\components\widgets\TicketsListWidget;
use yii\widgets\Pjax;
use mcms\partners\controllers\SupportController;
use mcms\partners\components\widgets\TicketCreateWidget;
use yii\widgets\ListView;

/** @var yii\data\ActiveDataProvider $tickets */

$ticketsArr = $tickets->getModels();
?>
<?php Pjax::begin([
  'id' => TicketsListWidget::PJAX_ID,
]); ?>

<?php if(count($ticketsArr) > 0): ?>

    <?= ListView::widget([
      'id' => 'accordion',
      'dataProvider' => $tickets,
      'options' => [
        'class' => 'panel-group tickets',
        'role' => 'tablist',
        'aria-multiselect' => 'true'
      ],
      'itemOptions' => [
        'tag' => false
      ],
      'layout' => '{items}
        <div class="content__position ticket-pg">
          <div class="row">
            <div class="col-xs-7">{pager}</div>
          </div>
        </div>',
      'itemView' => function($model){
        return TicketWidget::widget(['model' => $model]);
      }
    ])?>

<?php else: ?>
  <div class="empty_data empty_data-link">
    <div class="empty_data-icon">
      <i class="icon-file"></i>
      <a href="" data-toggle="modal" data-target="#<?= TicketCreateWidget::MODAL_ID ?>"><i class="icon-plus"></i></a>
    </div>
    <div class="empty_data-info">
      <span><?= Yii::_t('partners.main.no_data_available')?></span>
      <a href="" data-toggle="modal" data-target="#<?= TicketCreateWidget::MODAL_ID ?>"><?= SupportController::t('create_ticket') ?></a>
    </div>

  </div>
<?php endif; ?>


<?php Pjax::end(); ?>