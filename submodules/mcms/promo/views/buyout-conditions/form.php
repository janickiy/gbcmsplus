<?php
/** @var \yii\web\View $this */
/** @var BuyoutCondition $model */

use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\helpers\Html;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\components\widgets\LandingsDropdown;
use mcms\common\widget\UserSelect2;
use mcms\promo\models\BuyoutCondition;
use rgk\utils\widgets\modal\Modal;

$type1 = BuyoutCondition::TYPE_BUYOUT_MINUTES;
$type2 = BuyoutCondition::TYPE_IS_BUYOUT_ONLY_AFTER_1ST_REBILL;
$type3 = BuyoutCondition::TYPE_IS_BUYOUT_ONLY_UNIQUE_PHONE;

$js = <<<JS
\$('#buyoutcondition-type').change(function() {
  \$('.bc-types').hide();
  if (\$(this).val() == '$type1') {
    \$('#bm-field').show();
  }
  if (\$(this).val() == '$type2') {
    \$('#boar-field').show();
  }
  if (\$(this).val() == '$type3') {
    \$('#boup-field').show();
  }
});
JS;

$this->registerJs($js);

?>
<?php $form = AjaxActiveKartikForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#conditionsPjax'),
]) ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <?= $form->field($model, 'name') ?>
    <?= $form->field($model, 'operator_id')->widget(
      OperatorsDropdown::class,
      [
        'options' => [
          'prompt' => Yii::_t('app.common.not_selected'),
        ],
        'pluginOptions' => [
          'allowClear' => true,
        ],
        'useSelect2' => true,
      ]
    ) ?>
    <?= $form->field($model, 'user_id')->widget(UserSelect2::class, [
      'model' => $model,
      'attribute' => 'user_id',
      'initValueUserId' => $model->user_id,
      'roles' => ['partner'],
      'options' => [
        'placeholder' => '',
      ],
    ]) ?>
    <?= $form->field($model, 'landing_id')->widget(
      LandingsDropdown::class,
      [
        'options' => [
          'prompt' => Yii::_t('app.common.not_selected'),
        ],
        'pluginOptions' => [
          'allowClear' => true,
        ],
        'useSelect2' => true,
      ]
    ) ?>
    <?= $form->field($model, 'type')->dropDownList($model->getTypesList(), ['prompt' => '']) ?>
    <div id="bm-field" class="bc-types" <?=((int)$model->type === BuyoutCondition::TYPE_BUYOUT_MINUTES) ? '' : 'style="display:none;"'?>>
      <?= $form->field($model, 'buyout_minutes') ?>
    </div>
    <?php $yesNoList = [0 => Yii::_t('app.common.No'), 1 => Yii::_t('app.common.Yes')]; ?>
    <div id="boar-field" class="bc-types"<?=((int)$model->type === BuyoutCondition::TYPE_IS_BUYOUT_ONLY_AFTER_1ST_REBILL) ? '' : 'style="display:none;"'?>>
      <?= $form->field($model, 'is_buyout_only_after_1st_rebill')->radioList($yesNoList)->label(false); ?>
    </div>
    <div id="boup-field" class="bc-types"<?=((int)$model->type === BuyoutCondition::TYPE_IS_BUYOUT_ONLY_UNIQUE_PHONE) ? '' : 'style="display:none;"'?>>
      <?= $form->field($model, 'is_buyout_only_unique_phone')->radioList($yesNoList)->label(false); ?>
    </div>
  </div>

  <div class="modal-footer">
    <div class="row">
      <div class="col-md-12">
        <?= Html::submitButton('<i class="fa fa-save"></i> ' . Yii::_t('app.common.Create'), ['class' => 'pull-right btn btn-success']) ?>
      </div>
    </div>
  </div>
<?php AjaxActiveKartikForm::end() ?>