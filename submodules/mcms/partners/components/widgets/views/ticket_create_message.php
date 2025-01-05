<?php
use mcms\common\widget\AjaxButtons;
use mcms\partners\controllers\SupportController;
use mcms\common\form\AjaxActiveForm;
use mcms\common\helpers\Html;
use yii\helpers\Url;
use mcms\partners\components\widgets\TicketWidget;
use dosamigos\fileupload\FileUpload;
use yii\web\JsExpression;
use mcms\partners\components\widgets\TicketsListWidget;

/** @var \mcms\partners\models\TicketMessageForm $model */
/** @var \mcms\support\models\Support $ticket */
/** @var string $avatar */
/** @var string $formId */

$lang = Yii::$app->language;
$js = <<<JS
  $(".imperavi:not(.binded)").addClass("binded").redactor({
    lang: '$lang',
    buttonsHide: ['html']
  });
JS;

// Не получилось прицепить как виджет \vova07\imperavi\Widget. Виджет рендерится только на первом поле :(
$this->registerJs($js, $this::POS_END, 'uniqueScriptId');
?>
<div class="row">
  <div class="col-xs-6 ticket-mobile">
    <div class="ticket-message ticket-new_message">
      <div class="user_avatar">
        <img src="/img/avatar.png" alt="">
      </div>
      <div class="ticket-message_wrap">

        <?php $form = AjaxActiveForm::begin([
          'id' => $formId,
          'action' => Url::to(['support/send-message', 'ticketId' => $ticket->id]),
          'options' => [
            'enctype' => 'multipart/form-data'
          ],
          'ajaxSuccess' => 'function(response){
            if(response.success != false) $("#dialog_' . $ticket->id . '").find(".panel-body").html(response);
          }',
        ]); ?>

        <div class="ticket-message_wrap-header"><?= SupportController::t('make_answer'); ?></div>
        <div class="ticket-message_wrap-body">
          <div class="smile-content">
            <?= $form->field($model, 'text')->textarea([
              'class' => 'imperavi'
            ])->label(false)->error(false); ?>
          </div>

          <div class="submit-mobile">
            <?= Html::submitButton('', [
              'class' => 'icon-new_ticket',
              'encode' => false,
            ]) ?>
          </div>
        </div>
        <div class="ticket-message_wrap-footer">
          <div class="row">
            <div class="col-xs-6">
              <?= $form->field($model, 'files')->hiddenInput(['id' => 'ticket-files' . $ticket->id])->label(false); ?>
              <?= $form->field($model, 'images')->widget(FileUpload::class, [
                'useDefaultButton' => false,
                'options' => [
                  'accept' => 'image/*',
                  'id' => 'ticket-images-' . $ticket->id,
                  'data-id' => $ticket->id,
                ],
                'url' => '/partners/support/upload-file/',
                'clientEvents' => [
                  'fileuploaddone' => new JsExpression('function(e, data) {
                     $(".field-ticketmessageform-images").removeClass("has-error");
                     $(".field-ticketmessageform-images .help-block").hide();
                     if(data.result.error) {
                       $(".field-ticketmessageform-images").addClass("has-error");
                       $(".field-ticketmessageform-images .help-block").show().html(data.result.error);
                     } else {
                       $(".field-ticketmessageform-images").show().next().addClass("hide");
                       var $attached = $("#ticket-images' . $ticket->id . '");
                       $attached.find(".delete-file").data("url", data.result.file.deleteUrl)
                       $attached.prev().hide();
                       $attached.removeClass("hide");
                       $("#ticket-files' . $ticket->id . '").val(data.result.file.name);
                     }
                   }'),
                ]
              ])->hint('jpg, png, gif')->label(null, ['for' => 'ticket-images-' . $ticket->id]) ?>

              <div id="ticket-images<?= $ticket->id ?>" class="hide">
                <span><i class="icon-atach"></i> <?= Yii::_t('support.support-has_attached_file') ?>:</span>
                <div class="attach-img">
                  <img src="">
                  <button type="button" class="close delete-file"><i class="icon-cancel_4"></i></button>
                </div>
              </div>
            </div>
            <div class="col-xs-6 text-right">
              <?= Html::submitButton(SupportController::t('send'), [
                'class' => 'btn btn-success'
              ]) ?>
            </div>
          </div>
          <div class="row">
            <?= $form->errorSummary($model, ['class' => 'text-danger']) ?>
          </div>
        </div>


        <?php AjaxActiveForm::end(); ?>
      </div>
    </div>
  </div>
  <div class="col-xs-6 close_ticket_wrapper">
    <?php if ($ticket->is_opened): ?>
      <div class="close_ticket">
        <img src="/img/ticket-close.svg" alt="">
        <p><?= SupportController::t('you_can_close'); ?></p>
        <a href="javascript:void(0)"
           data-url="<?= Url::to(['support/close', 'id' => $ticket->id]) ?>"
        <?= AjaxButtons::CONFIRM_ATTRIBUTE ?>="<?= SupportController::t('confirm_close') ?>"
           data-pjax-update-after="<?= TicketsListWidget::PJAX_ID ?>"
        ><?= SupportController::t('close_ticket'); ?>
        </a>
      </div>
    <?php endif; ?>
  </div>
  <div class="col-xs-6 close_ticket_mobile_wrapper">
    <?php if ($ticket->is_opened): ?>
      <div class="close_ticket_mobile_inner">
        <a href="javascript:void(0)"
         data-url="<?= Url::to(['support/close', 'id' => $ticket->id]) ?>"
          <?= AjaxButtons::CONFIRM_ATTRIBUTE ?>="<?= SupportController::t('confirm_close') ?>"
          data-pjax-update-after="<?= TicketsListWidget::PJAX_ID ?>"
        ><?= SupportController::t('close_ticket'); ?>
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>
