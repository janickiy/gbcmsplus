<?php

use mcms\common\widget\modal\Modal;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\components\widgets\ProvidersDropdown;
use yii\helpers\Html;
use mcms\common\form\AjaxActiveForm;
use mcms\promo\models\TrafficBlock;

/**
 * @var yii\web\View $this
 * @var mcms\promo\models\TrafficBlock $model
 * @var yii\widgets\ActiveForm $form
 * @var bool $showUser
 */

$providerSelector = Html::getInputId($model, 'provider_id');
$operatorSelector = Html::getInputId($model, 'operator_id');

$js = <<<JS
$(function(){
  $(document).on('change', '#{$providerSelector}', function(e, v) {
    if (typeof v !== "undefined" && v.hasOwnProperty('skip')) { return }
    $('#{$operatorSelector}').val('').trigger('change', {skip: true});
  })
  $(document).on('change', '#{$operatorSelector}', function(e, v) {
    if (typeof v !== "undefined" && v.hasOwnProperty('skip')) { return }
    $('#{$providerSelector}').val('').trigger('change', {skip: true});
  })
})
JS;

$this->registerJs($js, \yii\web\View::POS_READY);
?>

<?php $form = AjaxActiveForm::begin([
  'action' => $model->isNewRecord ? ['/promo/traffic-block/create-modal'] : ['/promo/traffic-block/update-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#TrafficBlockGrid'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?=
    $model->isNewRecord
      ? Yii::_t('promo.traffic_block.create')
      : Yii::_t('promo.traffic_block.update')
    ?></h4>
</div>

<div class="modal-body">
  <?=
  $showUser
    ? $form->field($model, 'user_id')->widget('mcms\common\widget\UserSelect2', [
      'model' => $model,
      'attribute' => 'user_id',
      'initValueUserId' => $model->user_id,
      'roles' => ['partner'],
      'options' => [
        'placeholder' => '',
      ],
    ])
    : $form->field($model, 'user_id')->hiddenInput()->label(false)
  ?>
  <?= $form->field($model, 'provider_id')->widget(ProvidersDropdown::class, [
      'options' => [
        'prompt' => '',
      ],
      'useSelect2' => true,
      'pluginOptions' => [
        'allowClear' => true,
      ]
    ]
  ) ?>
  <?= $form->field($model, 'operator_id')->widget(
    OperatorsDropdown::class,
    [
      'options' => [
        'prompt' => '',
      ],
      'useSelect2' => true,
      'pluginOptions' => [
        'allowClear' => true,
      ]
    ]
  ) ?>
  <?= $form->field($model, 'is_blacklist')->radioList(TrafficBlock::getIsBlacklistOptions(), [
    'item' => function ($index, $label, $name, $checked, $value) {
      return '<div class="radio"><label>' . Html::radio($name, $checked, ['value' => $value]) . $label . '</label></div>';
    }]) ?>
  <?= $form->field($model, 'comment')->textarea() ?>
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