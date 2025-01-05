<?php

use mcms\common\widget\modal\Modal;
use mcms\promo\components\widgets\LandingsDropdown;
use yii\helpers\Html;
use mcms\promo\models\LandingRequestFilter;
use mcms\common\form\AjaxActiveForm;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var mcms\promo\models\LandingRequestFilter $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<?php $form = AjaxActiveForm::begin([
  'action' => $model->isNewRecord
    ? ['create-modal']
    : ['update-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#landingRequestFiltersGrid'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= LandingRequestFilter::translate('create') ?></h4>
</div>

<div class="modal-body">
  <?= $form->field($model, 'landing_id')->widget(
    LandingsDropdown::class,
    [
      'pluginOptions' => [
        'placeholder' => Yii::_t('app.common.not_selected'),
        'allowClear' => true,
        'ajax' => [
          'url' => Url::to(['landings/select2']),
          'dataType' => 'json',
        ],
      ],
      'useSelect2' => true,
    ]
  ) ?>

  <?= $form->field($model, 'code')->dropDownList(LandingRequestFilter::getProcessorLabels()) ?>

  <?= $form->field($model, 'is_active')->checkbox() ?>

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


