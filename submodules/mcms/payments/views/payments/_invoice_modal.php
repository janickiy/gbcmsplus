<?php
use mcms\common\form\AjaxActiveForm;
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\UserSelect2;
use mcms\payments\models\UserBalanceInvoice;

/** @var \mcms\user\Module $usersModule */
/** @var UserBalanceInvoice $model */
?>

<?php
$formId = 'invoiceForm';
$form = AjaxActiveForm::begin([
  'id' => $formId,
  'ajaxComplete' => 'function(){ 
    $("#modalWidget").modal("hide");
    $form = $("#' . $formId . '");
    $form.yiiActiveForm("resetForm");
    $form[0].reset();
  }',
]) ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>
  <div class="modal-body">
    <?= $form->field($model, 'currency')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'user_id')->widget(UserSelect2::class, [
      'initValueUserId' => $model->user_id,
      'userRowFormat' => '#:id: - :email: (:currency:)',
      'roles' => $usersModule::PARTNER_ROLE,
      'isActiveUsers' => true,
      'options' => [
          'placeholder' => Yii::_t('users.forms.enter_login_or_email') . ':',
        ],
      'readonly' => true,
    ]) ?>
    <?= $form->field($model, 'amount') ?>
    <?= $form->field($model, 'description')->textarea() ?>
  </div>
  <div class="modal-footer clearfix">
    <?= Html::button(Yii::_t('app.common.Close'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
    <?= Html::submitButton(Yii::_t('app.common.Add'), ['class' => 'btn btn-primary pull-right']) ?>
  </div>
<?php AjaxActiveForm::end() ?>