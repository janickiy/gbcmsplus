<?php
use mcms\partners\assets\basic\TicketsAsset;
use vova07\imperavi\Asset;
use mcms\partners\components\widgets\TicketsListWidget;
use mcms\partners\components\widgets\TicketCreateWidget;
use mcms\partners\controllers\SupportController;

$redactorAsset = Asset::register($this);
$lang = Yii::$app->language;
//Потому что нет файла для английского языка
if ($lang !== 'en') {
  $redactorAsset->addLanguage($lang);
}
TicketsAsset::register($this);
?>

<div class="container">
  <div class="bgf">
    <div class="title ticket-title">
      <span class="active h_link"><?= SupportController::t('my_tickets') ?></span>
      <a href="#" data-toggle="modal" data-target="#<?= TicketCreateWidget::MODAL_ID?>" class="h_link"><i class="icon-plus1"></i><?= SupportController::t('create_ticket') ?></a>
    </div>

    <?= TicketsListWidget::widget(); ?>

  </div>
</div>

<?= TicketCreateWidget::widget(); ?>
