<?php
/**
 * @var \mcms\promo\models\PartnerProgram $partnerProgram
 * @var \mcms\promo\models\UserPromoSetting $userPromoSetting
 */

use mcms\common\widget\modal\Modal;
use mcms\common\widget\UserSelect2;
use yii\helpers\Html;
use mcms\common\form\AjaxActiveForm;
use yii\helpers\Url;
?>

<?php $form = AjaxActiveForm::begin([
  'action' => ['/promo/partner-programs/link-partner', 'id' => $partnerProgram->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#partners-pjax'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $this->title ?></h4>
</div>

<div class="modal-body">
  <?= $form->field($userPromoSetting, 'user_id')->widget(UserSelect2::class, [
    'options' => ['placeholder' => Yii::_t('app.common.choose')],
    'url' => ['partner-programs/get-users-by-partner-program', 'id' => $partnerProgram->id]
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