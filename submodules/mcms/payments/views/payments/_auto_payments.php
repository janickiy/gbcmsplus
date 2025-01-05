<?php
use mcms\common\widget\alert\Alert;
use mcms\common\widget\Select2;
use mcms\common\form\AjaxActiveForm;
use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use yii\web\JsExpression;

/** @var \mcms\payments\models\AutoPayout $model */
?>

<?php $form = AjaxActiveForm::begin([
  'id' => 'generate-payments-form',
  'ajaxSuccess' => new JsExpression('function (response) {
    var successCount = response.data.successCount
      , failCount = response.data.failCount
      , merchantErrors = response.data.merchantErrors
    ;
    successCount && $.smallBox({
          "color": "rgb(115, 158, 115)",
          "title": "' . Yii::_t('app.common.operation_success') . ' x" + successCount,
          "sound": false,
          "iconSmall": "miniPic fa fa-check-circle bounce animated"
        });

    if (failCount) {
      for (var messageCode in merchantErrors) {
        $.smallBox({
          "color": "rgb(196, 106, 105)",
          "title": messageCode + " " + merchantErrors[messageCode],
          "sound": false,
          "iconSmall": "miniPic fa fa-warning shake animated"
        });
      }
    }

    !successCount && !failCount && '.Alert::warning(Yii::_t('payments.message-no-payments')).'
  }'),
  'ajaxComplete' => Modal::ajaxSuccess('#user-payments-grid'),
]) ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>
<div class="modal-body">
  <?= $form->field($model, 'wallet_type')->widget(Select2::class, [
    'data' => $model::getWalletTypes()
  ]) ?>
  <?= $form->field($model, 'status')->widget(Select2::class, [
    'data' => $model::getPayableStatuses(),
    'options' => [
      'multiple' => true,
    ]
  ]) ?>
</div>
  <div class="modal-footer">
    <div class="row">
      <div class="col-md-12">
        <?= Html::submitButton(
          Yii::_t('payments.payout'),
          ['id' => 'button-submit-modal', 'class' => 'btn btn-success']
        ) ?>
      </div>
    </div>
  </div>
<?php AjaxActiveForm::end() ?>