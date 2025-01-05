<?php

use mcms\common\helpers\ArrayHelper;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\Select2;
use mcms\promo\models\Source;
use yii\helpers\Html;
use mcms\common\form\AjaxActiveForm;
?>

<?php $form = AjaxActiveForm::begin([
  'action' => ['/promo/landing-sets/link-source/', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#webmaster-sources-pjax'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $this->title ?></h4>
</div>

<div class="modal-body">
  <?= $form->field($model, 'source_id')->widget('mcms\common\widget\Select2', [
    'data' => ArrayHelper::map(Source::getByCategory($model->category_id, $model->id), 'id', 'url'),
    'options' => ['placeholder' => Yii::_t('app.common.choose')],
    'pluginOptions' => [
      'allowClear' => true,
    ]
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


