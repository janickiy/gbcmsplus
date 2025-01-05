<?php
/**
 * @var \mcms\holds\models\HoldProgram $model
 * @var \mcms\holds\models\LinkPartnerForm $linkPartnerForm
 * @var \mcms\user\Module $userModule
 */

use mcms\common\widget\modal\Modal;
use mcms\common\widget\UserSelect2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use mcms\common\form\AjaxActiveForm;
use yii\helpers\Url;
?>

<?php $form = AjaxActiveForm::begin([
  'action' => ['/holds/partner-hold-rules/link-partner', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#partners-pjax'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $this->title ?></h4>
</div>

<div class="modal-body">
  <?= $form->field($linkPartnerForm, 'userId')->widget(UserSelect2::class, [
    'options' => ['placeholder' => Yii::_t('app.common.choose')],
    'roles' => [$userModule::PARTNER_ROLE],
    'ignoreIds' => ArrayHelper::getColumn($model->users, 'id'),
  ])->label(false) ?>
</div>

<div class="modal-footer">
  <div class="row">
    <div class="col-md-12">
      <?= Html::submitButton(
        '<i class="fa fa-save"></i> ' . Yii::_t('app.common.Save'),
        ['class' => 'btn btn-primary']
      ) ?>
    </div>
  </div>
</div>
<?php AjaxActiveForm::end(); ?>