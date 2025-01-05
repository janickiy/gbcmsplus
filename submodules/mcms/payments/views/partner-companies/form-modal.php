<?php

use mcms\payments\models\Company;
use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use rgk\utils\widgets\form\AjaxActiveForm;
use mcms\common\widget\UserSelect2;
use mcms\common\widget\Select2;

/** @var \mcms\payments\models\PartnerCompany $model */
?>

<?php $form = AjaxActiveForm::begin([
  'action' => $model->isNewRecord ? ['/payments/partner-companies/create'] : ['/payments/partner-companies/update-modal', 'id' => $model->id],
  'isFilesAjaxUpload' => true,
  'ajaxSuccess' => Modal::ajaxSuccess(null,
    "if ($('#partnerCompaniesPjaxGrid').length !== 0) {
      $.pjax.reload({container : '#partnerCompaniesPjaxGrid', 'timeout' : 5000});
    }
    if ($('#user-payment-settings-pjax-block').length !== 0) {
      $.pjax.reload({container : '#user-payment-settings-pjax-block', 'timeout' : 5000});
    }
    if ($('#user-payment-pjax-block').length !== 0) {
      $.pjax.reload({container : '#user-payment-pjax-block', 'timeout' : 5000});
    }
    if ($('#userpaymentform-user_wallet_id').length !== 0) {
      $('#userpaymentform-user_wallet_id').trigger('change');
    }"),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $model->name ? : Yii::_t('payments.company.create') ?></h4>
</div>

<div class="modal-body">
  <?= $form->field($model, 'name') ?>

  <?= $form->field($model, 'userIds')->widget(UserSelect2::class, [
    'theme' => Select2::THEME_SMARTADMIN,
    'showToggleAll' => false,
    'initValueUserId' => $model->userIds,
    'roles' => ['partner'],
    'options' => [
      'multiple' => true,
    ],
  ]) ?>

  <?= $form->field($model, 'reseller_company_id')->dropDownList(Company::getDropdownList()) ?>

  <?= $form->field($model, 'country') ?>

  <?= $form->field($model, 'city') ?>

  <?= $form->field($model, 'address') ?>

  <?= $form->field($model, 'post_code') ?>

  <?= $form->field($model, 'tax_code') ?>

  <?= $form->field($model, 'bank_entity') ?>

  <?= $form->field($model, 'bank_account')->textarea() ?>

  <?= $form->field($model, 'swift_code') ?>

  <?= $form->field($model, 'currency') ?>

  <?= $form->field($model, 'due_date_days_amount') ?>

  <?= $form->field($model, 'vat') ?>

  <?= $form->field($model, 'invoicing_cycle')->dropDownList($model::getInvoicingCycleDropdown(), [
    'prompt' => Yii::_t('app.common.not_selected'),
    'class' => 'form-control',
  ]) ?>

  <?= $model->agreement ? '' : $form->field($model, 'agreement')->fileInput(); ?>
</div>

<div class="modal-footer">
  <div class="row">
    <div class="col-md-12">
      <?= Html::submitButton(
        '<i class="fa fa-save"></i> ' . ($model->isNewRecord ? Yii::_t('app.common.Create') : Yii::_t('app.common.Save')),
        ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
      ) ?>
    </div>
  </div>
</div>
<?php AjaxActiveForm::end(); ?>



