<?php
use mcms\user\models\UserContact;
use rgk\utils\widgets\form\AjaxActiveForm;
use rgk\utils\widgets\modal\Modal;
use yii\helpers\Html;

/**
 * @var UserContact $model
 */

$this->title = $model->isNewRecord
  ? Yii::_t('users.forms.user_contacts_create')
  : Yii::_t('users.forms.user_contacts_update');

?>
<?php $form = AjaxActiveForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#userContactsPjaxGrid'),
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>
  <div class="modal-body">
    <div class="row">
      <div class="col-sm-8">
        <?= $form->field($model, 'type')->dropDownList($model::getTypes(), ['prompt' => Yii::_t('app.common.not_selected')]); ?>
        <?= $form->field($model, 'data'); ?>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => $model->isNewRecord ? 'btn btn-success pull-right' : 'btn btn-primary pull-right']) ?>
  </div>
<?php $form->end() ?>