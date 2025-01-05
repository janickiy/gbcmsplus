<?php
use mcms\common\form\AjaxActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$this->registerJs("
$('#passwordConfirmModal').on('shown.bs.modal', function () {
  $('#wallets-access-password').focus()
})
");
?>

<div class="modal fade" id="passwordConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

          <?php
          $successMessage = Yii::_t('partners.payments.check_password_success');
          $form = AjaxActiveForm::begin([
            'action' => Url::to(['payments/password-check']),
            'showMessageSuccess' => false,
            'ajaxSuccess' => new JsExpression(/** @lang JavaScript */ "function (data) {
  if (data.success) {
    PasswordConfirm._setConfirmed();
    if (PasswordConfirm.showAlertSuccess) {
      notifyInit('', '$successMessage', true);
    }
  }
}"),
            'ajaxError' => new JsExpression('function() { $("#wallets-access-password").val(""); }'),
          ]); ?>
            <input type="hidden" name="formUrl">
            <input type="hidden" name="pageName">
            <div class="modal-header">
                <button type="button" class="close" aria-label="Close"><i class="icon-cancel_4"></i></button>
                <h4 class="modal-title">
                  <?= Yii::_t('partners.main.enter_password')?>
                </h4>
            </div>
            <div class="modal-body">
              <?= Html::passwordInput('password', null, [
                'id' => 'wallets-access-password',
                'class' => 'form-control',
                'placeholder' => Yii::_t('partners.main.password'),
              ]) ?>
            </div>
            <div class="modal-footer">
                <button id="acceptPasswordConfirm" type="submit"
                        class="btn btn-success pull-left"><?= Yii::_t('partners.settings.continue') ?></button>
            </div>

          <?php AjaxActiveForm::end() ?>

        </div>
    </div>
</div>
