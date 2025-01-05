<?php
use mcms\common\form\AjaxActiveForm;
use mcms\partners\controllers\SupportController;
use mcms\partners\components\widgets\TicketCreateWidget;
use mcms\common\helpers\Html;
use mcms\partners\components\widgets\TicketsListWidget;
use dosamigos\fileupload\FileUpload;
use yii\web\JsExpression;

/** @var \mcms\partners\models\TicketForm $model */
/** @var array $ticketsCategories */

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

<!-- Modal -->
<div class="modal fade ticket-modal" id="<?= TicketCreateWidget::MODAL_ID?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <?php $form = AjaxActiveForm::begin([
        'id' => TicketCreateWidget::FORM_ID,
        'action' => '/partners/support/create/',
        'options' => [
          'enctype' => 'multipart/form-data'
        ],
        'ajaxSuccess' => 'function(response){
          $.pjax.reload("#' . TicketsListWidget::PJAX_ID . '");
          $("#' . TicketCreateWidget::MODAL_ID . '").modal("hide");
          $form = $("#' . TicketCreateWidget::FORM_ID . '");
          $form.yiiActiveForm("resetForm");
          $form[0].reset();
          $form.find(".redactor-editor").empty();
          $("#ticket-files").val(null);
          $("#ticket-images").empty();
        }',
      ]); ?>
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="icon-cancel_4"></i></button>
        <h4 class="modal-title" id="myModalLabel"><?= SupportController::t('create_ticket') ?></h4>
      </div>
      <div class="modal-body">
        <?= $form->field($model, 'name', [
          'inputOptions' => [
            'placeholder' => $model->getAttributeLabel('name'),
            'class' => 'form-control'
          ]
        ])->label(false); ?>
        <?= $form->field($model, 'support_category_id')->dropDownList($ticketsCategories, [
          'class' => 'selectpicker',
          'data-width' => '100%'
        ])->label(false); ?>
        <?= $form->field($model, 'text')->textArea([
          'placeholder' => $model->getAttributeLabel('text'),
          'class' => 'imperavi',
          'rows' => 5
        ])->label(false); ?>
      </div>
      <div class="modal-footer">
        <div class="row">
          <div class="col-xs-6 text-left">
            <?= $form->field($model, 'files')->hiddenInput(['id' => 'ticket-files'])->label(false);?>
            <?= $form->field($model, 'images')->widget(FileUpload::class, [
              'useDefaultButton' => false,
              'options' => [
                'accept' => 'image/*',
              ],
              'url' => '/partners/support/upload-file/',
              'clientEvents' => [
                'fileuploaddone' => new JsExpression('function(e, data) {
                     $(".field-ticketform-images").removeClass("has-error");
                     $(".field-ticketform-images .help-block").hide();
                     if(data.result.error) {
                       $(".field-ticketform-images").addClass("has-error");
                       $(".field-ticketform-images .help-block").show().html(data.result.error);
                     } else {
                       var $attached = $("#ticket-images");
                       $attached.find(".delete-file").data("url", data.result.file.deleteUrl);
                       $attached.prev().hide();
                       $attached.removeClass("hide");
                       $("#ticket-files").val(data.result.file.name);
                     }
                   }'),
              ]
            ])->hint('jpg, png, gif')?>
            <div id="ticket-images" class="ticket-message_wrap-footer hide">
              <span><i class="icon-atach"></i> <?= Yii::_t('support.support-has_attached_file') ?>:</span>
              <div class="attach-img">
                <img src="">
                <button type="button" class="close delete-file"><i class="icon-cancel_4"></i></button>
              </div>
            </div>
          </div>
          <div class="col-xs-6">
            <?= Html::submitButton(SupportController::t('create_ticket_btn'), [
              'class' => 'btn btn-success'
            ])?>
          </div>
        </div>

      </div>
      <?php AjaxActiveForm::end(); ?>

    </div>
  </div>
</div>