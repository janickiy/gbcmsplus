<?php

use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\multilang\widgets\multilangform\MultiLangForm;

$id = 'ads-types';

/** @var $model \mcms\promo\models\AdsType */
/** @var $this \mcms\common\web\View */

?>

<?php $form = AjaxActiveKartikForm::begin([
  'action' => $model->isNewRecord ? ['/promo/' . $id . '/create-modal'] : ['/promo/' . $id . '/update-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#' . $id . 'PjaxGrid'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $model->name ?: $model::translate('create') ?></h4>
</div>

<div class="modal-body">
  <?= MultiLangForm::widget([
    'model' => $model,
    'form' => $form,
    'attributes' => [
      'name' => ['type' => \kartik\builder\Form::INPUT_TEXT],
      'description' => ['type' => \kartik\builder\Form::INPUT_TEXTAREA],
    ]
  ]); ?>

  <?= $form->field($model, 'code', ['inputOptions' => ['disabled' => !$model->isNewRecord]]) ?>

  <div class="row">
    <div class="col-md-4">
      <?= $form->field($model, 'status')->dropDownList($model->statuses, [
        'prompt' => Yii::_t('app.common.not_selected')
      ]); ?>
    </div>
    <div class="col-md-4">
      <?= $form->field($model, 'security')->dropDownList($model->getAvailableSecurity(), [
        'prompt' => Yii::_t('app.common.not_selected')
      ]); ?>
    </div>
    <div class="col-md-4">
      <?= $form->field($model, 'profit')->dropDownList($model->getAvailableProfit(), [
        'prompt' => Yii::_t('app.common.not_selected')
      ]); ?>
    </div>
  </div>

  <?= $form->field($model, 'is_default')->checkbox(); ?>
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

<?php AjaxActiveKartikForm::end(); ?>


