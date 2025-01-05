<?php

use mcms\common\widget\UserSelect2;
use mcms\support\assets\SupportAdminAssets;
use yii\widgets\ListView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use mcms\common\helpers\Link;

/**
 * @var yii\web\View $this
 * @var mcms\support\models\Support $support
 * @var mcms\support\models\search\SupportSearch $searchModel
 * @var array $rolesAllowedToDelegate
 */
SupportAdminAssets::register($this);

$this->blocks['actions'] = null;

if ($support->has_unread_messages) {
  $this->blocks['actions'] .= $this->render('actions/read', ['model' => $support]);
}

if($support->is_opened) {
  $this->blocks['actions'] .= $this->render('actions/close', ['model' => $support]);
} else {
  $this->blocks['actions'] .= $this->render('actions/open', ['model' => $support]);
}

$this->registerJs('$(document).on("pjax:timeout", function(event) {
  // Prevent default timeout redirection behavior
  event.preventDefault()
});')
?>

<div class="row">
  <div class="col-md-8">
    <div class="panel panel-default">
      <div class="panel-body">
        <?php Pjax::begin(); ?>
          <?= ListView::widget([
            'dataProvider' => $messagesDataProvider,
            'itemView' => 'message'
          ])?>
        <?php Pjax::end(); ?>
      </div>
      <?php if($support->is_opened): ?>
      <div class="panel-footer">
        <?php $form = ActiveForm::begin([
          'id' => 'ticket-message-form',
          'options' => [
            'enctype' => 'multipart/form-data',
            'data-pjax' => false
          ]
        ]); ?>

        <?= $form->field($messageFormModel, 'text')->widget(
          \vova07\imperavi\Widget::class,
          [
            'settings' => [
              'buttonsHide' => ['html'],
              'minHeight' => '100px',
              'plugins' => ['fullscreen']
            ]
          ]
        ); ?>

        <?= $form->field($messageFormModel, 'images')->fileInput(['accept' => 'image/*']); ?>

        <hr>
        <div class="clearfix">
          <?= Html::button(Yii::_t('support.controller.ticket_view_send'), ['type' => 'submit', 'class' => 'btn btn-success btn-sm pull-right', 'id' => 'btn-chat']) ?>
        </div>

        <?php ActiveForm::end(); ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-md-4">
    <div class="panel panel-default">
      <div class="panel-body">
        <table class="table">
          <tr>
            <td style="width: 40%;"><strong><?= Yii::_t('support.controller.label_ticket_created')?>:</strong></td>
            <td><?= Yii::$app->formatter->asDatetime($support->created_at)?></td>
          </tr>
          <tr>
            <td style="width: 40%;"><strong><?= Yii::_t('support.controller.label_ticket_notRead')?>:</strong></td>
            <td><?= Yii::_t('support.controller.ticket_' . ($support->hasUnreadMessages() ? 'has' : 'hasNot') . 'UnreadMessages')?></td>
          </tr>
          <tr>
            <td style="width: 40%;"><strong><?= Yii::_t('support.controller.ticket_delegatedTo')?>:</strong></td>
            <td>
              <?php if ($rolesAllowedToDelegate):?>
                <?= UserSelect2::widget([
                  'name' => 'delegated_to',
                  'initValueUserId' => $support->delegated_to,
                  'value' => $support->delegated_to,
                  'roles' => $rolesAllowedToDelegate,
                  'disabled' => !Link::hasAccess('/support/tickets/delegate'),
                ]) ?>
                <?= Link::get('/support/tickets/delegate', ['id' => $support->id], ['class' => 'btn btn-link btn-xs', 'id' => 'delegate-button', 'style' => 'display: none'], Yii::_t('support.controller.ticket_view_delegateTo')) ?>
              <?php else:?>
                <?= Yii::_t('support.controller.ticket_delegete_to_users_not_found')?>
              <?php endif;?>
            </td>
          </tr>
          <tr>
            <td><strong><?= Yii::_t('support.controller.ticket_isOpened') ?>:</strong></td>
            <td><?= Yii::_t('support.controller.ticket_' . ($support->isOpened() ? 'opened' : 'closed')) ?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xs-12">
    <fieldset>
      <legend><?= Yii::_t('support.controller.ticket_view_history') ?></legend>
    </fieldset>

    <table class="table table-break-word">
      <thead>
      <tr>
        <th><?= Yii::_t('support.controller.ticket_created_at') ?></th>
        <th><?= Yii::_t('support.controller.ticket_view_history_created_by') ?></th>
        <th><?= Yii::_t('support.controller.ticket_category_label') ?></th>
        <th><?= Yii::_t('support.controller.ticket_delegatedTo') ?> <i class="glyphicon glyphicon-user"></i></th>
        <th><?= Yii::_t('support.controller.ticket_isOpened') ?></th>
      </tr>
      </thead>
      <tbody>
      <?php if($history):?>
        <?php $defaultCategory = $support->getSupportCategory()->one()->name?>
        <?php foreach($history as $historyItem):?>
          <?php $historyCategoryName = $historyItem->getSupportCategory()->one() ?>
          <tr>
            <td><?= Yii::$app->formatter->asDatetime($historyItem->created_at) ?></td>
            <td>
              <?php $createdBy = $historyItem->getCreatedBy()->one() ?>
              <?= Link::get('/users/users/view', ['id' => $createdBy->id], ['target' => '_blank'], Yii::$app->formatter->asText($createdBy->username)) ?>
            </td>
            <td>
              <?= $historyCategoryName ? $historyCategoryName->name : Yii::_t('yii.Not set'); ?>
            </td>
            <td>
              <?php $delegatedTo = $historyItem->getDelegatedTo()->one() ?>
              <?php if(!$delegatedTo): ?>
                <?= Yii::_t('support.controller.ticket_notDelegated') ?>
              <?php else:?>
                <?= Link::get('/users/users/view', ['id' => $delegatedTo->id], ['target' => '_blank'], $delegatedTo->username) ?>
              <?php endif?>
            </td>
            <td><?= Yii::_t('support.controller.ticket_' . ($historyItem->isOpened() ? 'opened' : 'closed')) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif;?>
      </tbody>
    </table>
  </div>
</div>
