<?php

use mcms\user\models\UserInvitationForm;
use rgk\utils\widgets\form\AjaxActiveForm;
use rgk\utils\widgets\modal\Modal;
use yii\helpers\Html;

/**
 * @var UserInvitationForm $model
 */

$this->title = Yii::_t('users.forms.users_invitations_create');

?>
<?php $form = AjaxActiveForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#usersInvitationsPjaxGrid'),
  'options' => [
    'enctype' => 'multipart/form-data',
  ],
  'isFilesAjaxUpload' => true,
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>
  <div class="modal-body">
    <div class="row">
      <div class="col-sm-12">
        <?= $form->field($model, 'data')->textarea(['rows' => 8]); ?>
        <?= $form->field($model, 'csvFile')->fileInput(); ?>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' =>'btn btn-success pull-right']) ?>
  </div>
<?php $form->end() ?>