<?php
/** @var \yii\web\View $this */
/** @var \mcms\promo\models\LandingSetItem $modelItem */
/** @var \mcms\promo\models\LandingSet $modelSet */

use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\helpers\Html;
use mcms\common\widget\Select2;
use mcms\promo\components\widgets\LandingsDropdown;
use mcms\promo\components\widgets\MultipleLandingsInsertWidget;
use mcms\promo\components\widgets\OperatorsDropdown;
use yii\helpers\Url;
use yii\web\JsExpression;

?>
<?php $form = AjaxActiveKartikForm::begin([
  'ajaxSuccess' => \mcms\common\widget\modal\Modal::ajaxSuccess('#landings-list')
]) ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <?= $form->field($modelItem, 'is_enabled')->checkbox() ?>
    <?= $form->field($modelItem, 'operator_id')->widget(
      OperatorsDropdown::class,
      [
        'pluginEvents' => [
          'change' => 'function() { $("#' . Html::getInputId($modelItem, 'landing_id') . '").val("").change(); }'
        ],
        'useSelect2' => true,
      ]
    ) ?>
    <?= $form->field($modelItem, 'landing_id')->widget(
      LandingsDropdown::class,
      [
        'pluginOptions' => [
          'placeholder' => Yii::_t('app.common.not_selected'),
          'allowClear' => true,
          'ajax' => [
            'url' => Url::to(['landings/select2', 'excludeSetId' => $modelSet->id, 'category_id' => $modelSet->category_id]),
            'dataType' => 'json',
            'data' => new JsExpression('function(params) {
            var operatorId = $("#' . Html::getInputId($modelItem, 'operator_id') . '").val();
            return {
              operatorRequired: 0,
              q: params.term ? params.term : "",
              operators: operatorId ? [operatorId] : [],
            };
          }')
          ],
        ],
        'useSelect2' => true,
      ]
    ) ?>

    <?= MultipleLandingsInsertWidget::widget([
      'model' => $modelItem,
      'form' => $form,
    ]) ?>

    <?= $form->field($modelItem, 'categoryId')->hiddenInput(['value' => $modelSet->category_id])->label(false) ?>
  </div>

  <div class="modal-footer">
    <div class="row">
      <div class="col-md-12">
        <?= Html::submitButton(
          '<i class="fa fa-save"></i> ' . ($modelItem->isNewRecord ? Yii::_t('app.common.Create') : Yii::_t('app.common.Save')),
          ['class' => $modelItem->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
      </div>
    </div>
  </div>
<?php AjaxActiveKartikForm::end() ?>