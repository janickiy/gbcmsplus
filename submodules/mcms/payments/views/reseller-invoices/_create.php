<?php
/**
 * @var $model mcms\payments\models\forms\ResellerConvertForm
 */

use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\widget\modal\Modal;
use mcms\payments\assets\ConvertorAssets;
use yii\bootstrap\Html;
use yii\helpers\Url;


ConvertorAssets::register($this);
?>

<?php $form = AjaxActiveKartikForm::begin([
  'id' => 'convert-form',
  'action' => Url::to(['convert-modal']),
  'ajaxSuccess' => Modal::ajaxSuccess('#reseller-payments'),
  'options' => ['data-convert-url' => Url::to(['convert'])],
]) ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">×</button>
  <h4 class="modal-title"><?= Yii::_t('payments.users.balance-conversion') ?></h4>
</div>

<div class="modal-body">
  <div class="row">
    <div class="col-sm-8">
      <?= $form->field($model, 'currencyFrom')->dropDownList($model->getCurrencies(), [
        'prompt' => Yii::_t('app.common.not_selected'),
      ]); ?>
    </div>
    <div class="col-sm-4">
      <?= $form->field($model, 'amountFrom')->hint(Yii::_t('payments.reseller-convert-form.balance-is') . ' <span></span>', ['class' => 'note hidden']) ?>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-8">
      <?= $form->field($model, 'currencyTo')->dropDownList($model->getCurrencies(), [
        'prompt' => Yii::_t('app.common.not_selected'),
      ]); ?>
    </div>
    <div class="col-sm-4">
      <?= Html::label(Yii::_t('payments.reseller-convert-form.amountTo')) ?>
      <?= Html::input('text', 'amountTo', null, [
        'id' => 'resellerconvertform-amountto',
        'class' => 'form-control',
        'disabled' => true,
      ]) ?>
    </div>
  </div>
</div>
<div class="modal-footer">
  <div class="row">
    <div class="col-md-12">
      <?= Html::submitButton(
        Html::icon('save', ['prefix' => 'fa fa-']) . ' ' . Yii::_t('payments.reseller-convert-form.order-convert'),
        ['class' => 'pull-right btn btn-success']
      ) ?>
    </div>
  </div>
</div>
<?php AjaxActiveKartikForm::end() ?>
