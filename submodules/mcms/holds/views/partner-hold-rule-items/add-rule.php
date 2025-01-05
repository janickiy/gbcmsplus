<?php

use kartik\widgets\DatePicker;
use mcms\holds\components\UnholdSettingsDescription;
use mcms\promo\models\Country;
use rgk\utils\widgets\form\AjaxActiveForm;
use yii\bootstrap\Html as BHtml;
use yii\helpers\Html;
use rgk\utils\widgets\modal\Modal;

/** @var $model \mcms\holds\models\HoldProgramRule */
/* @var $this yii\web\View */
$js = <<<JS
$('#holdprogramrule-unhold_range').change(function(){
  if ($(this).val() == 1) {
    $('#key_date_row').hide();
  } else {
    $('#key_date_row').show();
  }
});
JS;
$this->registerJs($js);
?>

<?php $form = AjaxActiveForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#hold_rules_list_pjax'),
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?=
      $model->isNewRecord
        ? Yii::_t('holds.main.add-rule')
        : Yii::_t('holds.main.update-rule')
      ?></h4>
  </div>

  <div class="modal-body">
    <div class="row">
      <div class="col-md-12">
        <?= $form->field($model, 'country_id')->dropDownList(Country::getDropdownItems(), ['prompt' => '-- All --']) ?>
      </div>
    </div>

    <hr>

    <p><a class="" role="button" data-toggle="collapse" href="#hold-rules-help" aria-expanded="false"
          aria-controls="hold-rules-help">
        <?= BHtml::icon('question-sign') ?> <?= Yii::_t('holds.main.add-rule-help-label') ?>
      </a></p>
    <div class="collapse" id="hold-rules-help">
      <div class="well">
        <?= Yii::_t('holds.main.add-rule-help') ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <?= $form->field($model, 'unhold_range') ?>
      </div>
      <div class="col-md-6">
        <?= $form
          ->field($model, 'unhold_range_type')
          ->dropDownList(UnholdSettingsDescription::getRangeTypeName(), ['prompt' => ''])
          ->label('&nbsp;') ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <?= $form->field($model, 'min_hold_range') ?>
      </div>
      <div class="col-md-6">
        <?= $form
          ->field($model, 'min_hold_range_type')
          ->dropDownList(UnholdSettingsDescription::getRangeTypeName(), ['prompt' => ''])
          ->label('&nbsp;') ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <?= $form->field($model, 'at_day')->label($model->getAttributeLabel('at_day') . ' (' . Yii::_t('holds.main.optional') . ')') ?>
      </div>
      <div class="col-md-6">
        <?= $form
          ->field($model, 'at_day_type')
          ->dropDownList(UnholdSettingsDescription::getAtDayTypeName(), ['prompt' => ''])
          ->label('&nbsp;') ?>
      </div>
    </div>
    <div id="key_date_row" class="row" <?php if ($model->isNewRecord || $model->unhold_range === 1): ?>style="display: none;" <?php endif;?>>
      <div class="col-md-12">
        <?= $form->field($model, 'key_date')->widget(DatePicker::class, [
          'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
          ],
        ]) ?>
      </div>
    </div>
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