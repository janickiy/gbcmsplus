<?php

use mcms\common\widget\modal\Modal;
use mcms\common\form\AjaxActiveForm;
use yii\bootstrap\Html;
use yii\web\JsExpression;
use yii\helpers\Url;
use mcms\common\helpers\ArrayHelper;

$id = 'domains';
?>

<?php $form = AjaxActiveForm::begin([
  'action' => $model->isNewRecord ? ['/promo/' . $id . '/create-modal'] : ['/promo/' . $id . '/update-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#' . $id . 'PjaxGrid'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $model->url ? Yii::$app->formatter->asText($model->url) : Yii::_t('promo.' . $id . '.create') ?></h4>
</div>

<div class="modal-body">
  <?= $form->errorSummary($model, ['class' => 'alert alert-danger']); ?>

  <div class="alert alert-info fade in">
    <?= Html::icon('info fa-fw', ['tag' => 'i', 'prefix' => 'fa fa-']) ?>
    <?= Yii::_t('domains.domain_ip'); ?>: <strong><?=$aDomainIp?></strong>
  </div>
  <?= $form->field($model, 'user_id')->widget('mcms\common\widget\UserSelect2', [
    'model' => $model,
    'attribute' => 'user_id',
    'initValueUserId' => $model->user_id,
    'skipCurrentUser' => true,
    'options' => [
      'placeholder' => '',
    ],
  ]) ?>
  <?= $form->field($model, 'url'); ?>
  <?= $form->field($model, 'status')->dropDownList($model->statuses, ['prompt' => Yii::_t('app.common.not_selected')]); ?>
  <?= $form->field($model, 'type')->dropDownList($model->types); ?>
  <?= $form->field($model, 'is_system')->checkbox(); ?>
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


